<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\CidrMask;
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

    public function matchARecord(string $ipAddress, string $domain, CidrMask $cidr): bool
    {
        $ipVersion = $this->ipMatcher->ipVersion($ipAddress);
        $aRecords = $this->resolveIpAddresses($domain, $ipVersion);

        foreach ($aRecords as $aRecord) {
            if ($this->ipMatcher->matchIpAddress($ipAddress, $aRecord['ip'], $this->getCidr($cidr, $ipVersion))) {
                return true;
            }
        }

        return false;
    }

    private function resolveIpAddresses(string $domain, int $ipVersion): array
    {
        if ($ipVersion == IpVersion::IPV6) {
            return $this->dnsLookupService->getAaaaRecords($domain);
        }

        return $this->dnsLookupService->getARecords($domain);
    }

    private function getCidr(CidrMask $cidr, int $ipVersion)
    {
        if ($ipVersion == IpVersion::IPV6) {
            return $cidr->getCidr2();
        }

        return $cidr->getCidr1();
    }
}
