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
        $parts = explode("/", $spfMechanism);
        $this->cidr = count($parts) > 1 ? (int)array_pop($parts) : 32;

        $parts = explode(":", $parts[0]);
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

    public function getCidr(): int
    {
        return $this->cidr;
    }
}
