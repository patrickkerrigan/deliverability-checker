<?php

namespace Pkerrigan\DeliverabilityChecker\UseCase\Response;

use Pkerrigan\DeliverabilityChecker\SpfResult;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 26/04/17
 */
class DeliverabilityResponse
{
    /**
     * @var bool
     */
    private $domainExists;
    /**
     * @var int
     */
    private $spfResult;

    public function __construct(bool $domainExists, int $spfResult = SpfResult::NONE)
    {
        $this->domainExists = $domainExists;
        $this->spfResult = $spfResult;
    }

    public function doesDomainExist(): bool
    {
        return $this->domainExists;
    }

    public function getSpfResult(): int
    {
        return $this->spfResult;
    }
}
