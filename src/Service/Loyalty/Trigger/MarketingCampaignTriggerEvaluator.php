<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/**
 * "Double/triple points pendant une campagne marketing". Config: campaignCode.
 * Fired externally via LoyaltyEngine::dispatchCustomEvent() passing
 * extra['campaignCode'], with no coupling from LoyaltyEngine to the
 * marketing module itself.
 */
final class MarketingCampaignTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'marketing_campaign';
    }

    public function getLabel(): string
    {
        return 'Campagne marketing';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $campaignCode = (string) ($rule->getTriggerConfig()['campaignCode'] ?? '');
        $activeCampaignCode = (string) ($context->extra['campaignCode'] ?? '');

        if ('' === $campaignCode || $campaignCode !== $activeCampaignCode) {
            return 0;
        }

        return $rule->getRewardPoints();
    }
}
