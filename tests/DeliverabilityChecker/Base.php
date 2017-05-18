<?php

namespace Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;

use PHPUnit\Framework\TestCase;
use Pkerrigan\DeliverabilityChecker\DeliverabilityChecker;
use Pkerrigan\DeliverabilityChecker\MockDnsLookupService;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 18/05/17
 */
class Base extends TestCase
{
    /**
     * @var MockDnsLookupService
     */
    protected $lookupService;
    /**
     * @var DeliverabilityChecker
     */
    protected $deliverabilityChecker;

    public function setUp()
    {
        parent::setUp();
        $this->lookupService = new MockDnsLookupService();
        $this->deliverabilityChecker = new DeliverabilityChecker($this->lookupService);
    }
}
