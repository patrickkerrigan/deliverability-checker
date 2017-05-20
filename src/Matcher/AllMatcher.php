<?php

namespace  Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\Matcher;
use Pkerrigan\DeliverabilityChecker\Mechanism;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class AllMatcher implements Matcher
{
    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === 'all';
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        return true;
    }
}
