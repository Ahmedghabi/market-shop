<?php

namespace App\Service\Loyalty\Dto;

/**
 * What the customer asked for at checkout: nothing, all points, a chosen
 * amount, or a specific reward — per LoyaltyProgram's allowed redemption modes.
 */
final class LoyaltyRedemptionRequest
{
    public function __construct(
        public readonly bool $useAllPoints = false,
        public readonly ?int $pointsToUse = null,
        public readonly ?string $rewardId = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return !$this->useAllPoints && null === $this->pointsToUse && null === $this->rewardId;
    }
}
