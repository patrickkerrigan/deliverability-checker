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
}
