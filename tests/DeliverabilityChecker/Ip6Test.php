<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 22/05/17
 */
class Ip6Test extends Base
{
    /**
     * @test
     */
    public function GivenDomainWithNonMatchingIp6SpfRecord_WhenCheckingAgainstIpAddress_ReturnsFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip6:::2 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '::1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingIp6SpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip6:::1 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '::1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithNonMatchingIp6CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip6:::/112 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '::1:1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingIp6CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip6:::/112 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '::1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingLargeIp6CidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 ip6:ffff:ffff:ffff:ffff:ffff:ffff:ffff::/112 -all");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }
}
