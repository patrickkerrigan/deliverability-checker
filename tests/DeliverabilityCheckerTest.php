<?php
namespace Pkerrigan\DeliverabilityChecker;
use PHPUnit\Framework\TestCase;

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


}