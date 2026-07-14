<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Commande gratuite avec montant maximum configurable. Config: maxOrderAmountCents. */
final class FreeOrderCappedRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'free_order_capped';
    }

    public function getLabel(): string
    {
        return 'Commande gratuite (plafonnée)';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        $maxOrderAmountCents = (int) ($reward->getConfig()['maxOrderAmountCents'] ?? 0);
        $discount = min($context->remainingSpendableCents(), $maxOrderAmountCents > 0 ? $maxOrderAmountCents : $context->remainingSpendableCents());

        return new LoyaltyRewardApplicationResult(discountCents: max(0, $discount));
    }
}
