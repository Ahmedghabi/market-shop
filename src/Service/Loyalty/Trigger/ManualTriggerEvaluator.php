<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/**
 * "Validation manuelle -> +Y points". Never fires during automatic order
 * evaluation; only awarded through LoyaltyEngine::manualAdjustment(), which
 * sets extra['manual'] = true.
 */
final class ManualTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'manual';
    }

    public function getLabel(): string
    {
        return 'Validation manuelle (attribution par un administrateur)';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        return true === ($context->extra['manual'] ?? false) ? $rule->getRewardPoints() : 0;
    }
}
