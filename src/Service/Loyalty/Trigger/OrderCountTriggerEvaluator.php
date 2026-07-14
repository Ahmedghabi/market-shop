<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/**
 * "Toutes les N commandes validées -> +Y points / débloque une récompense".
 * Config: ordersRequired.
 */
final class OrderCountTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'order_count';
    }

    public function getLabel(): string
    {
        return 'Nombre de commandes (jalon récurrent)';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $ordersRequired = (int) ($rule->getTriggerConfig()['ordersRequired'] ?? 0);

        if ($ordersRequired <= 0 || $context->customerOrderCount <= 0) {
            return 0;
        }

        if (0 !== $context->customerOrderCount % $ordersRequired) {
            return 0;
        }

        return $rule->getRewardPoints();
    }
}
