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
    public $aRecords = [];
    public $lastReceivedAQuery;
    public $aaaaRecords = [];
    public $lastReceivedAaaaQuery;
    public $mxRecords = [];
    public $lastReceivedMxQuery;

    public function getTxtRecords(string $domain): array
    {
        $this->lastReceivedTxtQuery = $domain;

        return $this->txtRecords[$domain] ?? [];
    }

    public function getSoaRecord(string $domain): array
    {
        $this->lastReceivedSoaQuery = $domain;

        return $this->soaRecord;
    }

    public function setSoaRecord()
    {
        $this->soaRecord = [
            [
                "host" => "example.org",
                "class" => "IN",
                "ttl" => 3600,
                "type" => "SOA",
            ]
        ];
    }

    public function addTxtRecord(string $domain, string $record)
    {
        $this->txtRecords[$domain][] = [
            "host" => $domain,
            "class" => "IN",
            "ttl" => 3600,
            "type" => "txt",
            "txt" => $record
        ];
    }

    public function getARecords(string $domain): array
    {
        $this->lastReceivedAQuery = $domain;

        return $this->aRecords[$domain] ?? [];
    }

    public function addARecord(string $domain, string $ipAddress)
    {
        $this->aRecords[$domain][] = [
            "host" => $domain,
            "class" => "IN",
            "ttl" => 3600,
            "type" => "A",
            "ip" => $ipAddress
        ];
    }

    public function getAaaaRecords(string $domain): array
    {
        $this->lastReceivedAaaaQuery = $domain;

        return $this->aaaaRecords[$domain] ?? [];
    }

    public function addAaaaRecord(string $domain, string $ipAddress)
    {
        $this->aaaaRecords[$domain][] = [
            "host" => $domain,
            "class" => "IN",
            "ttl" => 3600,
            "type" => "AAAA",
            "ip" => $ipAddress
        ];
    }

    public function getMxRecords(string $domain): array
    {
        $this->lastReceivedMxQuery = $domain;

        return $this->mxRecords[$domain] ?? [];
    }

    public function addMxRecord(string $domain, string $mx)
    {
        $this->mxRecords[$domain][] = [
            "host" => $domain,
            "class" => "IN",
            "ttl" => 3600,
            "type" => "MX",
            "target" => $mx
        ];
    }
}
