<?php
namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 26/04/17
 */
class MockDnsLookupService implements DnsLookupService
{
    public $txtRecords = [];
    public $lastReceivedTxtQuery;

    public $soaRecord = [];
    public $lastReceivedSoaQuery;

    public function getTxtRecords(string $domain): array
    {
        $this->lastReceivedTxtQuery = $domain;
        return $this->txtRecords;
    }

    public function getSoaRecord(string $domain): array
    {
        $this->lastReceivedSoaQuery = $domain;
        return $this->soaRecord;
    }

    public function setSoaRecord()
    {
        $this->soaRecord = [[
            "host" => "example.org",
            "class" => "IN",
            "ttl" => 3600,
            "type" => "SOA",
        ]];
    }
}
