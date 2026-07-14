<?php

namespace App\Service\Loyalty\Dto;

/**
 * Compute-only result of applying a LoyaltyReward: discount cents plus flags
 * and metadata for checkout/frontend to act on (no OrderItem mutation here).
 */
final class LoyaltyRewardApplicationResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public readonly int $discountCents = 0,
        public readonly bool $freeShipping = false,
        public readonly ?string $freeProductId = null,
        public readonly array $metadata = [],
    ) {
    }
}
