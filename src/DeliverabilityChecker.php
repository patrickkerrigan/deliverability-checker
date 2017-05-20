<?php

namespace Pkerrigan\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\Exception\ExcessiveDnsLookupsException;
use Pkerrigan\DeliverabilityChecker\Matcher\AllMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\AMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\Ip4Matcher;
use Pkerrigan\DeliverabilityChecker\Matcher\MxMatcher;
use Pkerrigan\DeliverabilityChecker\UseCase\CheckDeliverability;
use Pkerrigan\DeliverabilityChecker\UseCase\Response\DeliverabilityResponse;
use Pkerrigan\DeliverabilityChecker\UseCase\Response\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
class DeliverabilityChecker implements CheckDeliverability
{
    const DNS_LOOKUP_LIMIT = 10;
    const MODIFIERS = [
        "+" => SpfResult::PASS,
        "-" => SpfResult::HARDFAIL,
        "~" => SpfResult::SOFTFAIL,
        "?" => SpfResult::NEUTRAL
    ];
    /** @var DnsLookupService */
    private $lookupService;
    /** @var  int */
    private $dnsLookupCount = 0;
    /** @var Matcher[] */
    private $matchers = [];

    public function __construct(DnsLookupService $lookupService)
    {
        $this->lookupService = $lookupService;
        $this->matchers[] = new AllMatcher();
        $ip4Matcher = new Ip4Matcher();
        $this->matchers[] = $ip4Matcher;
        $aMatcher = new AMatcher($lookupService, $ip4Matcher);
        $this->matchers[] = $aMatcher;
        $this->matchers[] = new MxMatcher($lookupService, $aMatcher);
    }

    public function checkDeliverabilityFromIp(string $sourceEmailAddress, string $ipAddress): DeliverabilityResponse
    {
        $this->resetDnsLookupCount();
        $domain = $this->getDomainFromEmailAddress($sourceEmailAddress);

        try {
            return $this->checkIpAgainstDomain($ipAddress, $domain);
        } catch (ExcessiveDnsLookupsException $e) {
            return $this->spfResponse(SpfResult::ERROR);
        }
    }

    public function checkDeliverabilityFromIncludedSpfRecord(
        string $sourceEmailAddress,
        string $spfRecord
    ): DeliverabilityResponse {
        return new DeliverabilityResponse(true);
    }

    private function getDomainFromEmailAddress(string $sourceEmailAddress): string
    {
        $addressParts = explode('@', $sourceEmailAddress, 2);
        $domain = array_pop($addressParts);

        return $domain;
    }

    private function getSpfRecords(string $domain): array
    {
        $this->enforceDnsLookupLimit();

        return array_filter($this->lookupService->getTxtRecords($domain), function (array $record): bool {
            return isset($record['txt']) && mb_stripos($record['txt'], "v=spf1 ") === 0;
        });
    }

    private function noDomainResponse(): DeliverabilityResponse
    {
        return new DeliverabilityResponse(false);
    }

    private function spfResponse(int $spfResult): DeliverabilityResponse
    {
        return new DeliverabilityResponse(true, $spfResult);
    }

    private function getModifierResult(string $modifier = "+"): int
    {
        if (in_array($modifier, array_keys(self::MODIFIERS))) {
            return self::MODIFIERS[$modifier];
        }

        return SpfResult::PASS;
    }

    private function checkIpAgainstDomain(string $ipAddress, string $domain): DeliverabilityResponse
    {
        $soaRecord = $this->lookupService->getSoaRecord($domain);

        if (empty($soaRecord)) {
            return $this->noDomainResponse();
        }

        $spfRecords = $this->getSpfRecords($domain);

        if (empty($spfRecords)) {
            return $this->spfResponse(SpfResult::NONE);
        }

        $mechanisms = explode(" ", array_shift($spfRecords)["txt"]);
        array_shift($mechanisms);

        return $this->matchIpAgainstMechanisms($ipAddress, $domain, $mechanisms);
    }

    private function matchIpAgainstMechanisms(
        string $ipAddress,
        string $domain,
        array $mechanisms
    ): DeliverabilityResponse {
        foreach ($mechanisms as $mechanism) {
            $firstCharacter = mb_substr($mechanism, 0, 1);
            if (in_array($firstCharacter, array_keys(self::MODIFIERS))) {
                $mechanism = mb_substr($mechanism, 1);
            }

            if ($this->matchesMechanism($mechanism, $ipAddress, $domain)) {
                return $this->spfResponse($this->getModifierResult($firstCharacter));
            }
        }

        return $this->spfResponse(SpfResult::NEUTRAL);
    }

    private function matchesMechanism(string $mechanism, string $ipAddress, string $domain): bool
    {
        $mechanism = new Mechanism($mechanism);

        if ($mechanism->getMechanism() === "include" &&
            $this->checkIpAgainstDomain($ipAddress, $mechanism->getValue())->getSpfResult() == SpfResult::PASS
        ) {
            return true;
        }

        foreach ($this->matchers as $matcher) {
            if ($matcher->canHandle($mechanism)) {
                return $matcher->matches($mechanism, $ipAddress, $domain);
            }
        }

        return false;
    }

    private function resetDnsLookupCount()
    {
        $this->dnsLookupCount = 0;
    }

    private function enforceDnsLookupLimit()
    {
        if ($this->dnsLookupCount >= self::DNS_LOOKUP_LIMIT) {
            throw new ExcessiveDnsLookupsException();
        }

        $this->dnsLookupCount++;
    }
}
