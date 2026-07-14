<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/** "Achat d'une marque -> +Y points". Config: brandId. */
final class BrandPurchaseTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'brand_purchase';
    }

    public function getLabel(): string
    {
        return 'Achat d\'une marque';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $brandId = (string) ($rule->getTriggerConfig()['brandId'] ?? '');
        if ('' === $brandId || null === $context->order) {
            return 0;
        }

        foreach ($context->order->getItems() as $item) {
            $brand = $item->getProduct()?->getBrand();
            if (null !== $brand && (string) $brand->getId() === $brandId) {
                return $rule->getRewardPoints();
            }
        }

        return 0;
    }
}
