<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/** "Anniversaire -> +Y points". Fires when today matches the customer's birth month/day. */
final class BirthdayTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'birthday';
    }

    public function getLabel(): string
    {
        return 'Anniversaire du client';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $birthDate = $context->customer?->getBirthDate();
        if (null === $birthDate) {
            return 0;
        }

        $matches = $birthDate->format('m-d') === $context->now->format('m-d');

        return $matches ? $rule->getRewardPoints() : 0;
    }
}
