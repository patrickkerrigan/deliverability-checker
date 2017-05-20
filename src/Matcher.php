<?php

namespace Pkerrigan\DeliverabilityChecker;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
interface Matcher
{
    public function canHandle(Mechanism $mechanism): bool;

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool;
}
