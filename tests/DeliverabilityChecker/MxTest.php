<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\UseCase\Response\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class MxTest extends Base
{
    /**
     * @test
     */
    public function GivenDomainWithNonMatchingMxSpfRecord_WhenCheckingAgainstIpAddress_ReturnsFailResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 mx -all");
        $this->lookupService->addARecord('mx.example.org', '127.0.0.2');
        $this->lookupService->addMxRecord('example.org', 'mx.example.org');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::HARDFAIL, $result->getSpfResult());
    }

    /**
     * @test
     */
    public function GivenDomainWithMatchingMxSpfRecord_WhenCheckingAgainstIpAddress_ReturnsPassResponse()
    {
        $this->lookupService->setSoaRecord();
        $this->lookupService->addTxtRecord('example.org', "v=spf1 mx -all");
        $this->lookupService->addARecord('mx.example.org', '127.0.0.1');
        $this->lookupService->addMxRecord('example.org', 'mx.example.org');
        $result = $this->deliverabilityChecker->checkDeliverabilityFromIp('example.org', '127.0.0.1');

        $this->assertEquals(SpfResult::PASS, $result->getSpfResult());
    }
}
