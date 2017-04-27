<?php
namespace Pkerrigan\DeliverabilityChecker;
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
            return $this->noDomainResponse();
        }

        $spfRecords = $this->getSpfRecords($domain);

        if(empty($spfRecords)) {
            return $this->spfResponse(SpfResult::NONE);
        }

        return $this->spfResponse(SpfResult::ERROR);
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

    private function getSpfRecords(string $domain): array
    {
        return array_filter($this->lookupService->getTxtRecords($domain), function(array $record): bool {
            return isset($record['txt']) && stripos($record['txt'], "v=spf1 ") === 0;
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
}