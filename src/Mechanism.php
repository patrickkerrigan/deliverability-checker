<?php

namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class Mechanism
{
    private $mechanism;
    private $value;
    private $cidr;

    public function __construct(string $spfMechanism)
    {
        $cidr2Parts = explode("//", $spfMechanism);
        $cidr2 = count($cidr2Parts) > 1 ? (int)array_pop($cidr2Parts) : null;

        $parts = explode("/", $spfMechanism);
        $cidr1 = count($parts) > 1 ? (int)array_pop($parts) : null;

        $this->cidr = new CidrMask($cidr1, $cidr2);

        $parts = explode(":", $parts[0], 2);
        $this->mechanism = $parts[0];
        $this->value = $parts[1] ?? null;
    }

    public function getMechanism(): string
    {
        return $this->mechanism;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getCidr(): CidrMask
    {
        return $this->cidr;
    }
}
