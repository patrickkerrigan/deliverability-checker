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
    public function testCanConstructClass() {
        $checker = new DeliverabilityChecker();
        $this->assertInstanceOf(DeliverabilityChecker::class, $checker);
    }
}