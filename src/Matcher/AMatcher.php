<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\DnsLookupService;
use Pkerrigan\DeliverabilityChecker\IpVersion;
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
     * @var IpMatcher
     */
    private $ipMatcher;

    public function __construct(DnsLookupService $dnsLookupService, IpMatcher $ipMatcher)
    {
        $this->dnsLookupService = $dnsLookupService;
        $this->ipMatcher = $ipMatcher;
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
        $aRecords = $this->resolveIpAddresses($domain, $ipAddress);

        foreach ($aRecords as $aRecord) {
            if ($this->ipMatcher->matchIpAddress($ipAddress, $aRecord['ip'], $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function resolveIpAddresses(string $domain, string $ipAddress): array
    {
        if ($this->ipMatcher->ipVersion($ipAddress) == IpVersion::IPV6) {
            return $this->dnsLookupService->getAaaaRecords($domain);
        }

        return $this->dnsLookupService->getARecords($domain);
    }
}
