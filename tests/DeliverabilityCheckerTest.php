<?php

namespace Pkerrigan\DeliverabilityChecker;

use PHPUnit\Framework\TestCase;
use Pkerrigan\DeliverabilityChecker\UseCase\Response\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
class DeliverabilityCheckerTest extends TestCase
{
    /**
     * @var MockDnsLookupService
     */
    private $lookupService;
    /**
     * @var DeliverabilityChecker
     */
    private $deliverabilityChecker;

    public function setUp()
    {
        parent::setUp();
        $this->lookupService = new MockDnsLookupService();
        $this->deliverabilityChecker = new DeliverabilityChecker($this->lookupService);
    }

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
    public function GivenDomainWithHardFailAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsHardFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithSoftFailAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsSoftFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ~all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::SOFTFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithNeutralAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsNeutralResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ?all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::NEUTRAL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithPassAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 +all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithIncludedPassAllAndFailAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 include:example.net -all");
        $this->lookupService->addTxtRecord('example.net',"v=spf1 all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithRecursivelyIncludedPassAllAndFailAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 include:example.net -all");
        $this->lookupService->addTxtRecord('example.net',"v=spf1 include:example.co.uk -all");
        $this->lookupService->addTxtRecord('example.co.uk',"v=spf1 all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMoreThanTenDnsLookups_WhenCheckingAgainstIpAddress_ReturnsErrorResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 include:example1.net -all");
        $this->lookupService->addTxtRecord('example1.net',"v=spf1 include:example2.net -all");
        $this->lookupService->addTxtRecord('example2.net',"v=spf1 include:example3.net -all");
        $this->lookupService->addTxtRecord('example3.net',"v=spf1 include:example4.net -all");
        $this->lookupService->addTxtRecord('example4.net',"v=spf1 include:example5.net -all");
        $this->lookupService->addTxtRecord('example5.net',"v=spf1 include:example6.net -all");
        $this->lookupService->addTxtRecord('example6.net',"v=spf1 include:example7.net -all");
        $this->lookupService->addTxtRecord('example7.net',"v=spf1 include:example8.net -all");
        $this->lookupService->addTxtRecord('example8.net',"v=spf1 include:example9.net -all");
        $this->lookupService->addTxtRecord('example9.net',"v=spf1 include:example10.net -all");
        $this->lookupService->addTxtRecord('example10.net',"v=spf1 all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::ERROR, $result->getSpfResult());
    }
}
