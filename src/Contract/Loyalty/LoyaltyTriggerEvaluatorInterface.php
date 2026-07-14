<?php

namespace App\Contract\Loyalty;

use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Implement this interface to add a new loyalty earning trigger. Any class
 * implementing it is automatically registered with LoyaltyTriggerRegistry —
 * no edits to LoyaltyEngine, entities, or other evaluators are required.
 */
#[AutoconfigureTag('app.loyalty.trigger_evaluator')]
interface LoyaltyTriggerEvaluatorInterface
{
    /**
     * Unique code stored in LoyaltyRule::$triggerCode (e.g. "order_amount").
     */
    public function getCode(): string;

    /**
     * Human-readable label for the admin rule-builder UI.
     */
    public function getLabel(): string;

    /**
     * Returns the number of points earned for this rule/context, or 0 when
     * the rule does not apply. Never throws — return 0 for "not applicable".
     */
    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int;
}
