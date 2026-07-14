<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/** "Achat d'un produit spécifique -> +Y points". Config: productId. */
final class ProductPurchaseTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'product_purchase';
    }

    public function getLabel(): string
    {
        return 'Achat d\'un produit spécifique';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $productId = (string) ($rule->getTriggerConfig()['productId'] ?? '');
        if ('' === $productId || null === $context->order) {
            return 0;
        }

        foreach ($context->order->getItems() as $item) {
            if ((string) $item->getProduct()?->getId() === $productId) {
                return $rule->getRewardPoints();
            }
        }

        return 0;
    }
}
