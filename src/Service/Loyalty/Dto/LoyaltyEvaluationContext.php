<?php

namespace App\Service\Loyalty\Dto;

use App\Entity\Boutique;
use App\Entity\Customer;
use App\Entity\CustomerLoyalty;
use App\Entity\Order;

/**
 * Immutable value object passed to trigger evaluators and reward appliers so
 * they never need to query repositories themselves — LoyaltyEngine resolves
 * everything up front, keeping extensions simple, stateless and testable.
 */
final class LoyaltyEvaluationContext
{
    /**
     * @param array<string, mixed> $extra Free-form payload for custom_event / marketing_campaign / manual triggers
     */
    public function __construct(
        public readonly Boutique $boutique,
        public readonly ?Customer $customer = null,
        public readonly ?CustomerLoyalty $customerLoyalty = null,
        public readonly ?Order $order = null,
        public readonly int $subtotalCents = 0,
        public readonly int $alreadyAppliedDiscountsCents = 0,
        public readonly int $customerOrderCount = 0,
        public readonly \DateTimeImmutable $now = new \DateTimeImmutable(),
        public readonly array $extra = [],
    ) {
    }

    public function remainingSpendableCents(): int
    {
        return max(0, $this->subtotalCents - $this->alreadyAppliedDiscountsCents);
    }
}
