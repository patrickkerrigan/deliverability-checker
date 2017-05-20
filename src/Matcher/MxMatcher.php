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
class MxMatcher implements Matcher
{
    /**
     * @var DnsLookupService
     */
    private $dnsLookupService;
    /**
     * @var AMatcher
     */
    private $aMatcher;

    public function __construct(DnsLookupService $dnsLookupService, AMatcher $aMatcher)
    {
        $this->dnsLookupService = $dnsLookupService;
        $this->aMatcher = $aMatcher;
    }

    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === "mx";
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        $mxRecords = $this->dnsLookupService->getMxRecords($mechanism->getValue() ?: $domain);

        foreach ($mxRecords as $mxRecord) {
            if ($this->aMatcher->matchARecord($ipAddress, $mxRecord['target'], $mechanism->getCidr())) {
                return true;
            }
        }

        return false;
    }
}
