<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 19/05/17
 */
class ATest extends Base
{
    /**
     * @test
     */
    public function GivenDomainWithNonMatchingASpfRecord_WhenCheckingAgainstIpAddress_ReturnsFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 a -all");
        $this->lookupService->addARecord('example.org', '127.0.0.2');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingASpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 a -all");
        $this->lookupService->addARecord('example.org', '127.0.0.1');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingASpfRecordForAnotherDomain_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 a:example.co.uk -all");
        $this->lookupService->addARecord('example.co.uk', '127.0.0.1');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingACidrSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 a/24 -all");
        $this->lookupService->addARecord('example.org', '127.0.0.1');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.2');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }
}
