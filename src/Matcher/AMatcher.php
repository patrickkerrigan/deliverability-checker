<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\DnsLookupService;
use Pkerrigan\DeliverabilityChecker\Matcher;
use Pkerrigan\DeliverabilityChecker\Mechanism;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class AMatcher implements Matcher
{
    /**
     * @var DnsLookupService
     */
    private $dnsLookupService;
    /**
     * @var Ip4Matcher
     */
    private $ip4Matcher;

    public function __construct(DnsLookupService $dnsLookupService, Ip4Matcher $ip4Matcher)
    {
        $this->dnsLookupService = $dnsLookupService;
        $this->ip4Matcher = $ip4Matcher;
    }

    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === "a";
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        return $this->matchARecord($ipAddress, $mechanism->getValue() ?: $domain, $mechanism->getCidr());
    }

    public function matchARecord(string $ipAddress, string $domain, int $cidr): bool
    {
        $aRecords = $this->dnsLookupService->getARecords($domain);

        foreach ($aRecords as $aRecord) {
            if ($this->ip4Matcher->matchIpv4Address($ipAddress, $aRecord['ip'], $cidr)) {
                return true;
            }
        }

        return false;
    }
}
