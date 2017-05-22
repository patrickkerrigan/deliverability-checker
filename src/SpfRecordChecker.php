<?php

namespace Pkerrigan\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\Matcher\AllMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\AMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\IncludeMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\IpMatcher;
use Pkerrigan\DeliverabilityChecker\Matcher\MxMatcher;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class SpfRecordChecker
{
    const DNS_LOOKUP_LIMIT = 10;
    const MODIFIERS = [
        "+" => SpfResult::PASS,
        "-" => SpfResult::HARDFAIL,
        "~" => SpfResult::SOFTFAIL,
        "?" => SpfResult::NEUTRAL
    ];

    /**
     * @var DnsLookupService
     */
    private $lookupService;
    /**
     * @var Matcher[]
     */
    private $matchers = [];

    public function __construct(DnsLookupService $lookupService)
    {
        $this->lookupService = new LimitingDnsLookupService($lookupService, self::DNS_LOOKUP_LIMIT);
        $this->matchers[] = new AllMatcher();
        $this->matchers[] = new IncludeMatcher($this);
        $ipMatcher = new IpMatcher();
        $this->matchers[] = $ipMatcher;
        $aMatcher = new AMatcher($this->lookupService, $ipMatcher);
        $this->matchers[] = $aMatcher;
        $this->matchers[] = new MxMatcher($this->lookupService, $aMatcher);
    }

    private function getSpfRecords(string $domain): array
    {
        return array_filter($this->lookupService->getTxtRecords($domain), function (array $record): bool {
            return isset($record['txt']) && mb_stripos($record['txt'], "v=spf1 ") === 0;
        });
    }

    private function getModifierResult(string $modifier = "+"): int
    {
        if (in_array($modifier, array_keys(self::MODIFIERS))) {
            return self::MODIFIERS[$modifier];
        }

        return SpfResult::PASS;
    }

    public function checkIpAgainstDomain(string $ipAddress, string $domain): int
    {
        $spfRecords = $this->getSpfRecords($domain);

        if (empty($spfRecords)) {
            return SpfResult::NONE;
        }

        $mechanisms = explode(" ", array_shift($spfRecords)["txt"]);
        array_shift($mechanisms);

        return $this->matchIpAgainstMechanisms($ipAddress, $domain, $mechanisms);
    }

    public function checkSpf(string $ipAddress, string $domain): int
    {
        $this->lookupService->resetLookupCount();
        return $this->checkIpAgainstDomain($ipAddress, $domain);
    }

    private function matchIpAgainstMechanisms(string $ipAddress, string $domain, array $mechanisms): int
    {
        foreach ($mechanisms as $mechanism) {
            $firstCharacter = mb_substr($mechanism, 0, 1);
            if (in_array($firstCharacter, array_keys(self::MODIFIERS))) {
                $mechanism = mb_substr($mechanism, 1);
            }

            if ($this->matchesMechanism($mechanism, $ipAddress, $domain)) {
                return $this->getModifierResult($firstCharacter);
            }
        }

        return SpfResult::NEUTRAL;
    }

    private function matchesMechanism(string $mechanism, string $ipAddress, string $domain): bool
    {
        $mechanism = new Mechanism($mechanism);

        foreach ($this->matchers as $matcher) {
            if ($matcher->canHandle($mechanism)) {
                return $matcher->matches($mechanism, $ipAddress, $domain);
            }
        }

        return false;
    }
}
