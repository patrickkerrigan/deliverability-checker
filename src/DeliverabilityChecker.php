<?php

namespace Pkerrigan\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\Exception\ExcessiveDnsLookupsException;
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

    public function __construct(DnsLookupService $lookupService)
    {
        $this->lookupService = $lookupService;
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

        return $this->matchIpAgainstMechanisms($ipAddress, $mechanisms);
    }

    private function matchIpAgainstMechanisms(string $ipAddress, array $mechanisms): DeliverabilityResponse
    {
        foreach ($mechanisms as $mechanism) {
            $firstCharacter = mb_substr($mechanism, 0, 1);
            if (in_array($firstCharacter, array_keys(self::MODIFIERS))) {
                $mechanism = mb_substr($mechanism, 1);
            }

            if ($this->matchesMechanism($mechanism, $ipAddress)) {
                return $this->spfResponse($this->getModifierResult($firstCharacter));
            }
        }

        return $this->spfResponse(SpfResult::NEUTRAL);
    }

    private function matchesMechanism(string $mechanism, string $ipAddress): bool
    {
        $parts = explode(":", $mechanism);
        $mechanism = $parts[0];
        $value = isset($parts[1]) ? $parts[1] : null;

        switch ($mechanism) {
            case "all":
                return true;
                break;

            case "include":
                if ($this->checkIpAgainstDomain($ipAddress, $value)->getSpfResult() == SpfResult::PASS) {
                    return true;
                }
                break;

            case "ip4":
                return $this->ipv4Matches($value, $ipAddress);
                break;
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

    private function ipv4Matches(string $value, string $ipAddress): bool
    {
        $parts = explode("/", $value);
        $matchIp = $parts[0];
        $matchCidr = isset($parts[1]) ? (int)$parts[1] : 32;

        $ipAddress = ip2long($ipAddress);
        $matchIp = ip2long($matchIp);

        $mask = 0xffffff << (32 - $matchCidr);

        return ($matchIp & $mask) == ($ipAddress & $mask);
    }
}
