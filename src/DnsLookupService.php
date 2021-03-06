<?php

namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 25/04/17
 */
interface DnsLookupService
{
    public function getTxtRecords(string $domain): array;

    public function getSoaRecord(string $domain): array;

    public function getARecords(string $domain): array;

    public function getAaaaRecords(string $domain): array;

    public function getMxRecords(string $domain): array;
}
