<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use Pkerrigan\DeliverabilityChecker\UseCase\Response\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 18/05/17
 */
class IncludeTest extends Base
{
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
