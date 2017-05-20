<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\Matcher;
use Pkerrigan\DeliverabilityChecker\Mechanism;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class Ip4Matcher implements Matcher
{
    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === "ip4";
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        return $this->matchIpv4Address($ipAddress, $mechanism->getValue(), $mechanism->getCidr());
    }

    public function matchIpv4Address(string $ipAddress, string $allowedIpAddress, int $cidr): bool
    {
        $ipAddress = ip2long($ipAddress);
        $allowedIpAddress = ip2long($allowedIpAddress);

        $mask = 0xffffff << (32 - $cidr);

        return ($allowedIpAddress & $mask) == ($ipAddress & $mask);
    }
}
