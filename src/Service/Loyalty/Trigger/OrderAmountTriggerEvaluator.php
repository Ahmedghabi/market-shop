<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/**
 * "Tous les X DT d'achat -> +Y points". Config: amountThresholdCents, pointsPerThreshold.
 */
final class OrderAmountTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'order_amount';
    }

    public function getLabel(): string
    {
        return 'Montant de commande (tous les X DT dépensés)';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $config = $rule->getTriggerConfig();
        $threshold = (int) ($config['amountThresholdCents'] ?? 0);
        $pointsPerThreshold = (int) ($config['pointsPerThreshold'] ?? $rule->getRewardPoints());

        if ($threshold <= 0 || null === $context->order) {
            return 0;
        }

        $amount = $context->order->getSubtotalCents();

        return intdiv($amount, $threshold) * $pointsPerThreshold;
    }
}
