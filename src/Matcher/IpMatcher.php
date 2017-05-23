<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\IpVersion;
use Pkerrigan\DeliverabilityChecker\Matcher;
use Pkerrigan\DeliverabilityChecker\Mechanism;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class IpMatcher implements Matcher
{
    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === "ip4" || $mechanism->getMechanism() === "ip6";
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        return $this->matchIpAddress($ipAddress, $mechanism->getValue(), $mechanism->getCidr()->getCidr1());
    }

    public function matchIpAddress(string $ipAddress, string $allowedIpAddress, int $cidr = null): bool
    {
        $ipAddressBytes = $this->getBinaryOctets($ipAddress);
        $allowedAddressBytes = $this->getBinaryOctets($allowedIpAddress);

        if (count($ipAddressBytes) !== count($allowedAddressBytes)) {
            return false;
        }

        $maskBytes = $this->getMaskOctetsFromCidr($cidr ?: count($ipAddressBytes) * 8);

        return $this->matchAddressesWithMask($maskBytes, $allowedAddressBytes, $ipAddressBytes);
    }

    public function ipVersion(string $ipAddress): int
    {
        $bytes = $this->getBinaryOctets($ipAddress);
        return count($bytes) === 4 ? IpVersion::IPV4 : IpVersion::IPV6;
    }

    private function getBinaryOctets(string $ipAddress): array
    {
        return str_split(inet_pton($ipAddress));
    }

    private function getMaskOctetsFromCidr(int $cidr): array
    {
        $fullBytesInMask = intdiv($cidr, 8);
        $bitsInPartialByte = $cidr % 8;

        $maskBytes = array_fill(0, $fullBytesInMask, 0xff);

        if ($bitsInPartialByte > 0) {
            $maskBytes[] = 0xff << (8 - $bitsInPartialByte);
        }

        return $maskBytes;
    }

    private function matchAddressesWithMask(array $mask, array $ipAddressOne, array $ipAddressTwo): bool
    {
        for ($i = 0; $i < count($mask); $i++) {
            if ((ord($ipAddressOne[$i]) & $mask[$i]) !== (ord($ipAddressTwo[$i]) & $mask[$i])) {
                return false;
            }
        }

        return true;
    }
}
