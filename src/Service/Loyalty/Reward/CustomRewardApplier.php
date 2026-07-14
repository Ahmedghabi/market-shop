<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;

/**
 * Récompense personnalisée: passthrough of the full config as metadata plus
 * an optional flat discountCents, for boutiques/plugins with bespoke needs.
 */
final class CustomRewardApplier implements LoyaltyRewardApplierInterface
{
    public function getCode(): string
    {
        return 'custom';
    }

    public function getLabel(): string
    {
        return 'Récompense personnalisée';
    }

    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult
    {
        $config = $reward->getConfig();
        $discount = min((int) ($config['discountCents'] ?? 0), $context->remainingSpendableCents());

        return new LoyaltyRewardApplicationResult(
            discountCents: max(0, $discount),
            freeShipping: true === ($config['freeShipping'] ?? false),
            freeProductId: isset($config['freeProductId']) ? (string) $config['freeProductId'] : null,
            metadata: $config,
        );
    }
}
