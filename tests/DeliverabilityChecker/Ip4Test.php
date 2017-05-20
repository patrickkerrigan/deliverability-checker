<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 18/05/17
 */
class Ip4Test extends Base
{
    /**
     * @test
     */
    public function GivenDomainWithNonMatchingIp4SpfRecord_WhenCheckingAgainstIpAddress_ReturnsFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip4:127.0.0.2 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingIp4SpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip4:127.0.0.1 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithNonMatchingIp4CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip4:127.0.0.0/24 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.1.1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingIp4CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip4:127.0.0.0/24 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingLargeIp4CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip4:255.255.255.0/24 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '255.255.255.254');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }
}
