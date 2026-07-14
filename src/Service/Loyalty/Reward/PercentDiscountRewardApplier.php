<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Remise en pourcentage. Config: percent (0-100). */
final class PercentDiscountRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'percent_discount';
    }

    public function getLabel(): string
    {
        return 'Remise en pourcentage';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        $percent = max(0, min(100, (int) ($reward->getConfig()['percent'] ?? 0)));
        $discount = (int) round($context->remainingSpendableCents() * $percent / 100);

        if (null !== $reward->getMaxDiscountCents()) {
            $discount = min($discount, $reward->getMaxDiscountCents());
        }

        return new LoyaltyRewardApplicationResult(discountCents: max(0, $discount));
    }
}
