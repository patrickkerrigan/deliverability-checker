<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
class DnsTest extends Base
{

    /**
     * @test
     */
    public function GivenEmailAddress_WhenCheckingAgainstIpAddress_QueriesDnsForSoaRecord()
    {
        $this->deliverabilityChecker->checkDeliverabilityFromIp('test@example.org', '127.0.0.1');

        $this->assertEquals('example.org', $this->lookupService->lastReceivedSoaQuery);
    }

    /**
     * @test
     */
    public function GivenDomain_WhenCheckingAgainstIpAddress_QueriesDnsForSoaRecord()
    {
        $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals('example.org', $this->lookupService->lastReceivedSoaQuery);
    }

    /**
     * @test
     */
    public function GivenDomainWithNoSoaRecord_WhenCheckingAgainstIpAddress_DoesNotQueryDnsForTxtRecord()
    {
        $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertNull($this->lookupService->lastReceivedTxtQuery);
    }

    /**
     * @test
     */
    public function GivenDomainWithNoSoaRecord_WhenCheckingAgainstIpAddress_ReturnsDomainDoesNotExistResponse()
    {
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertFalse($result->doesDomainExist());
    }

    /**
     * @test
     */
    public function GivenDomainWithSoaRecord_WhenCheckingAgainstIpAddress_QueriesDnsForTxtRecord()
    {
        $this->lookupService->setSoaRecord();
        $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals("example.org", $this->lookupService->lastReceivedTxtQuery);
    }

    /**
     * @test
     */
    public function GivenDomainWithSoaRecord_WhenCheckingAgainstIpAddress_ReturnsDomainExistsResponse()
    {
        $this->lookupService->setSoaRecord();
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertTrue($result->doesDomainExist());
    }

    /**
     * @test
     */
    public function GivenDomainWithNoTxtRecord_WhenCheckingAgainstIpAddress_ReturnsNoneResponse()
    {
        $this->lookupService->setSoaRecord();
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::NONE, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithNoSpfRecord_WhenCheckingAgainstIpAddress_ReturnsNoneResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"not an SPF record");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::NONE, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMultipleSpfRecords_WhenCheckingAgainstIpAddress_ReturnsPermerrorResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 -all");
        $this->lookupService->addTxtRecord('example.org',"v=spf1 +all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PERMERROR, $result->getSpfResult());
    }
}
