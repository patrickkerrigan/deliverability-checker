<?php

namespace Pkerrigan\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\Exception\ExcessiveDnsLookupsException;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class LimitingDnsLookupService implements DnsLookupService
{
    /**
     * @var int
     */
    private $lookupLimit;
    /**
     * @var int
     */
    private $lookupCount = 0;
    /**
     * @var DnsLookupService
     */
    private $lookupService;

    public function __construct(DnsLookupService $lookupService, int $lookupLimit)
    {
        $this->lookupService = $lookupService;
        $this->lookupLimit = $lookupLimit;
    }

    public function getTxtRecords(string $domain): array
    {
        $this->enforceLimit();

        return $this->lookupService->getTxtRecords($domain);
    }

    public function getSoaRecord(string $domain): array
    {
        $this->enforceLimit();

        return $this->lookupService->getSoaRecord($domain);
    }

    public function getARecords(string $domain): array
    {
        $this->enforceLimit();

        return $this->lookupService->getARecords($domain);
    }

    public function getAaaaRecords(string $domain): array
    {
        $this->enforceLimit();

        return $this->lookupService->getAaaaRecords($domain);
    }

    public function getMxRecords(string $domain): array
    {
        $this->enforceLimit();

        return $this->lookupService->getMxRecords($domain);
    }

    public function resetLookupCount()
    {
        $this->lookupCount = 0;
    }

    private function enforceLimit()
    {
        if ($this->lookupCount >= $this->lookupLimit) {
            throw new ExcessiveDnsLookupsException();
        }

        $this->lookupCount++;
    }
}
