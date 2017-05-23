<?php

namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 23/05/17
 */
class CidrMask
{
    /**
     * @var int|null
     */
    private $cidr1;
    /**
     * @var int|null
     */
    private $cidr2;

    public function __construct(int $cidr1 = null, int $cidr2 = null)
    {

        $this->cidr1 = $cidr1;
        $this->cidr2 = $cidr2;
    }

    public function getCidr1()
    {
        return $this->cidr1;
    }

    public function getCidr2()
    {
        return $this->cidr2;
    }
}
