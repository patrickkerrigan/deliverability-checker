<?php

namespace Pkerrigan\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\Exception\ExcessiveDnsLookupsException;
use Pkerrigan\DeliverabilityChecker\UseCase\CheckDeliverability;
use Pkerrigan\DeliverabilityChecker\UseCase\Response\DeliverabilityResponse;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
class DeliverabilityChecker implements CheckDeliverability
{
    /**
     * @var SpfRecordChecker
     */
    public $spfRecordChecker;
    /**
     * @var DnsLookupService
     */
    private $lookupService;

    public function __construct(DnsLookupService $lookupService)
    {
        $this->lookupService = $lookupService;
        $this->spfRecordChecker = new SpfRecordChecker($lookupService);
    }

    public function checkDeliverabilityFromIp(string $sourceEmailAddress, string $ipAddress): DeliverabilityResponse
    {
        $domain = $this->getDomainFromEmailAddress($sourceEmailAddress);

        $soaRecord = $this->lookupService->getSoaRecord($domain);

        if (empty($soaRecord)) {
            return $this->noDomainResponse();
        }

        try {
            return $this->spfResponse($this->spfRecordChecker->checkSpf($ipAddress, $domain));
        } catch (ExcessiveDnsLookupsException $e) {
            return $this->spfResponse(SpfResult::ERROR);
        }
    }

    private function getDomainFromEmailAddress(string $sourceEmailAddress): string
    {
        $addressParts = explode('@', $sourceEmailAddress, 2);
        $domain = array_pop($addressParts);

        return $domain;
    }

    private function noDomainResponse(): DeliverabilityResponse
    {
        return new DeliverabilityResponse(false);
    }

    private function spfResponse(int $spfResult): DeliverabilityResponse
    {
        return new DeliverabilityResponse(true, $spfResult);
    }
}
