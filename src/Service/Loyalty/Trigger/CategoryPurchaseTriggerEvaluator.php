<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use App\Entity\LoyaltyRule;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;

/** "Achat d'une catégorie -> +Y points". Config: categoryId. */
final class CategoryPurchaseTriggerEvaluator implements LoyaltyTriggerEvaluatorInterface
{
    public function getCode(): string
    {
        return 'category_purchase';
    }

    public function getLabel(): string
    {
        return 'Achat dans une catégorie';
    }

    public function evaluate(LoyaltyRule $rule, LoyaltyEvaluationContext $context): int
    {
        $categoryId = (string) ($rule->getTriggerConfig()['categoryId'] ?? '');
        if ('' === $categoryId || null === $context->order) {
            return 0;
        }

        foreach ($context->order->getItems() as $item) {
            $category = $item->getProduct()?->getCategory();
            if (null !== $category && (string) $category->getId() === $categoryId) {
                return $rule->getRewardPoints();
            }
        }

        return 0;
    }
}
