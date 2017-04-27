<?php

namespace Pkerrigan\DeliverabilityChecker\UseCase\Response;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 27/04/17
 */
class SpfResult
{
    const PASS = 0;
    const SOFTFAIL = 1;
    const HARDFAIL = 2;
    const NEUTRAL = 3;
    const NONE = 4;
    const ERROR = 5;
}
