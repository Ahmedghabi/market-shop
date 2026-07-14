<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Remise fixe. Config: amountCents. */
final class FixedDiscountRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'fixed_discount';
    }

    public function getLabel(): string
    {
        return 'Remise fixe';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        $amountCents = (int) ($reward->getConfig()['amountCents'] ?? 0);
        $discount = min($amountCents, $context->remainingSpendableCents());

        if (null !== $reward->getMaxDiscountCents()) {
            $discount = min($discount, $reward->getMaxDiscountCents());
        }

        return new LoyaltyRewardApplicationResult(discountCents: max(0, $discount));
    }
}
