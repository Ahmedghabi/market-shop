<?php

namespace App\Service\Loyalty\Dto;

/**
 * Result of a redemption calculation/quote — never persisted directly, used
 * by CartService to apply the discount and by the checkout "quote" endpoint.
 */
final class LoyaltyCalculationResult
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public readonly bool $success,
        public readonly int $pointsUsed = 0,
        public readonly int $discountCents = 0,
        public readonly int $newSubtotalCents = 0,
        public readonly bool $freeShipping = false,
        public readonly ?string $freeProductId = null,
        public readonly ?string $rewardId = null,
        public readonly ?string $errorMessage = null,
        public readonly array $metadata = [],
    ) {
    }

    public static function failure(string $message): self
    {
        return new self(success: false, errorMessage: $message);
    }
}
