<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/** "Premier achat -> +Y points". */
final class FirstPurchaseTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'first_purchase';
    }

    public function getLabel(): string
    {
        return 'Premier achat';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        return 1 === $context->customerOrderCount ? $rule->getRewardPoints() : 0;
    }
}
