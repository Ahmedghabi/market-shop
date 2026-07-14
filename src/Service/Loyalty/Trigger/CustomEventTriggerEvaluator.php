<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/**
 * "Événement personnalisé -> +Y points". Config: eventCode. Any module can
 * grant points by calling LoyaltyEngine::dispatchCustomEvent($eventCode, ...)
 * without any coupling to LoyaltyEngine internals.
 */
final class CustomEventTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'custom_event';
    }

    public function getLabel(): string
    {
        return 'Événement personnalisé';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $eventCode = (string) ($rule->getTriggerConfig()['eventCode'] ?? '');
        $dispatchedCode = (string) ($context->extra['eventCode'] ?? '');

        if ('' === $eventCode || $eventCode !== $dispatchedCode) {
            return 0;
        }

        return $rule->getRewardPoints();
    }
}
