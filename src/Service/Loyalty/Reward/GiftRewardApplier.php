<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/** Cadeau générique (non catalogué). Config: description, arbitrary payload. */
final class GiftRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'gift';
    }

    public function getLabel(): string
    {
        return 'Cadeau';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        unset($context);

        return new LoyaltyRewardApplicationResult(metadata: $reward->getConfig());
    }
}
