<?php

namespace Pkerrigan\DeliverabilityChecker\UseCase;

use Pkerrigan\DeliverabilityChecker\UseCase\Response\DeliverabilityResponse;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 24/04/17
 */
interface CheckDeliverability
{
    public function checkDeliverabilityFromIp(string $sourceEmailAddress, string $ipAddress): DeliverabilityResponse;
}
