<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Produit offert. Config: productId. Compute-only: checkout adds the free line item. */
final class FreeProductRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'free_product';
    }

    public function getLabel(): string
    {
        return 'Produit offert';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        unset($context);
        $productId = $reward->getConfig()['productId'] ?? null;

        return new LoyaltyRewardApplicationResult(
            freeProductId: null !== $productId ? (string) $productId : null,
        );
    }
}
