<?php

namespace Pkerrigan\DeliverabilityChecker\Matcher;

use Pkerrigan\DeliverabilityChecker\Matcher;
use Pkerrigan\DeliverabilityChecker\Mechanism;
use Pkerrigan\DeliverabilityChecker\SpfRecordChecker;
use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 20/05/2017
 */
class IncludeMatcher implements Matcher
{
    /**
     * @var SpfRecordChecker
     */
    private $spfRecordChecker;

    public function __construct(SpfRecordChecker $spfRecordChecker)
    {
        $this->spfRecordChecker = $spfRecordChecker;
    }

    public function canHandle(Mechanism $mechanism): bool
    {
        return $mechanism->getMechanism() === "include";
    }

    public function matches(Mechanism $mechanism, string $ipAddress, string $domain): bool
    {
        return $this->spfRecordChecker->checkIpAgainstDomain($ipAddress, $mechanism->getValue()) == SpfResult::PASS;
    }
}
