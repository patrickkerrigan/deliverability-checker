<?php
namespace Pkerrigan\DeliverabilityChecker;
use Pkerrigan\DeliverabilityChecker\UseCase\CheckDeliverability;
use Pkerrigan\DeliverabilityChecker\UseCase\Response\DeliverabilityResponse;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
class DeliverabilityChecker implements CheckDeliverability
{
    /** @var DnsLookupService */
    private $lookupService;

    public function __construct(DnsLookupService $lookupService)
    {
        $this->lookupService = $lookupService;
    }

    public function checkDeliverabilityFromIp(string $sourceEmailAddress, string $ipAddress): DeliverabilityResponse
    {
        $domain = $this->getDomainFromEmailAddress($sourceEmailAddress);
        $soaRecord = $this->lookupService->getSoaRecord($domain);

        if(empty($soaRecord)){
            return new DeliverabilityResponse(false);
        }

        $txtRecords = $this->lookupService->getTxtRecords($domain);
        return new DeliverabilityResponse(true);
    }

    public function checkDeliverabilityFromIncludedSpfRecord(string $sourceEmailAddress, string $spfRecord): DeliverabilityResponse {
        return new DeliverabilityResponse(true);
    }

    private function getDomainFromEmailAddress(string $sourceEmailAddress): string
    {
        $addressParts = explode('@', $sourceEmailAddress, 2);
        $domain = array_pop($addressParts);
        return $domain;
    }
}