<?php

namespace Pkerrigan\DeliverabilityChecker\UseCase\Response;

/**
 *
 * @author Patrick Kerrigan <patrick@patrickkerrigan.uk>
 * @since 26/04/17
 */
class DeliverabilityResponse
{
    private $domainExists;
    private $spfResult;

    public function __construct(bool $domainExists, int $spfResult = SpfResult::ERROR)
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
