<?php
namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 26/04/17
 */
class MockDnsLookupService implements DnsLookupService
{

    public function getTxtRecords(string $domain): array
    {
        return [];
    }
}
