<?php

namespace Pkerrigan\DeliverabilityChecker;

use PHPUnit\Framework\TestCase;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 21/05/2017
 */
class LimitingDnsLookupServiceTest extends TestCase
{
    const LOOKUP_LIMIT = 2;

    /**
     * @var LimitingDnsLookupService
     */
    public $limitingLookupService;
    /**
     * @var MockDnsLookupService
     */
    private $lookupService;

    public function setUp()
    {
        parent::setUp();
        $this->lookupService = new MockDnsLookupService();
        $this->limitingLookupService = new LimitingDnsLookupService($this->lookupService, self::LOOKUP_LIMIT);
    }

    /**
     * @test
     */
    public function GivenLimitingLookupService_WhenSoaRecordLookedUp_PassesThroughToLookupService()
    {
        $this->limitingLookupService->getSoaRecord("example.org");

        $this->assertEquals("example.org", $this->lookupService->lastReceivedSoaQuery);
    }

    /**
     * @test
     */
    public function GivenLimitingLookupService_WhenTxtRecordLookedUp_PassesThroughToLookupService()
    {
        $this->limitingLookupService->getTxtRecords("example.org");

        $this->assertEquals("example.org", $this->lookupService->lastReceivedTxtQuery);
    }

    /**
     * @test
     */
    public function GivenLimitingLookupService_WhenARecordLookedUp_PassesThroughToLookupService()
    {
        $this->limitingLookupService->getARecords("example.org");

        $this->assertEquals("example.org", $this->lookupService->lastReceivedAQuery);
    }

    /**
     * @test
     */
    public function GivenLimitingLookupService_WhenMxRecordLookedUp_PassesThroughToLookupService()
    {
        $this->limitingLookupService->getMxRecords("example.org");

        $this->assertEquals("example.org", $this->lookupService->lastReceivedMxQuery);
    }

    /**
     * @test
     * @expectedException \Pkerrigan\DeliverabilityChecker\Exception\ExcessiveDnsLookupsException
     */
    public function GivenLimitingLookupService_WhenLimitExceeded_ThrowsException()
    {
        $this->limitingLookupService->getMxRecords("example.org");
        $this->limitingLookupService->getARecords("example.org");
        $this->limitingLookupService->getTxtRecords("example.org");
    }

    /**
     * @test
     */
    public function GivenLimitingLookupService_WhenLimitReachedAndResetCalled_AllowsMoreCalls()
    {
        $this->limitingLookupService->getMxRecords("example.org");
        $this->limitingLookupService->getARecords("example.org");
        $this->limitingLookupService->resetLookupCount();
        $this->limitingLookupService->getTxtRecords("example.org");
        $this->assertTrue(true);
    }
}
