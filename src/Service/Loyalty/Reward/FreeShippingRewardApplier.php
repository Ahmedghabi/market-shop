<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Livraison gratuite. No monetary discount here — delivery module reads the freeShipping flag. */
final class FreeShippingRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'free_shipping';
    }

    public function getLabel(): string
    {
        return 'Livraison gratuite';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        unset($context);

        return new LoyaltyRewardApplicationResult(freeShipping: true);
    }
}
