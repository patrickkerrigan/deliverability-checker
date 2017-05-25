<?php

namespace Pkerrigan\DeliverabilityChecker;

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
    const PERMERROR = 5;
    const TEMPERROR = 6;
}
