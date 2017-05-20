<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\UseCase\Response\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 18/05/17
 */
class AllTest extends Base
{
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
    public function GivenDomainWithNoAllSpfRecord_WhenCheckingAgainstIpAddress_ReturnsNeutralResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org',"v=spf1 a");
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::NEUTRAL, $result->getSpfResult());
    }
}
