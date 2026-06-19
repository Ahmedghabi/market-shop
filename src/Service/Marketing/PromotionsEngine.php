<?php

namespace App\Service\Marketing;

use App\Entity\Promotion;
use App\Enum\PromotionType;
use App\Repository\PromotionRepository;
use App\Repository\BoutiqueRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PromotionsEngine
{
    public function __construct(
        private PromotionRepository $promotions,
        private BoutiqueRepository $boutiques,
        private EntityManagerInterface $em,
    ) {
    }

    public function calculateBestDiscount(
        string $boutiqueId,
        int $cartAmountCents,
        array $productIds = [],
        array $categoryIds = [],
    ): int {
        $boutique = $this->boutiques->find($boutiqueId);
        if (!$boutique) {
            return 0;
        }

        $activePromotions = $this->promotions->findActiveByBoutique($boutique);
        $bestDiscount = 0;

        foreach ($activePromotions as $promotion) {
            $discount = $this->calculateDiscount($promotion, $cartAmountCents, $productIds, $categoryIds);
            $bestDiscount = max($bestDiscount, $discount);
        }

        return $bestDiscount;
    }

    public function calculateAllDiscounts(
        string $boutiqueId,
        int $cartAmountCents,
        array $productIds = [],
        array $categoryIds = [],
    ): array {
        $boutique = $this->boutiques->find($boutiqueId);
        if (!$boutique) {
            return [];
        }

        $activePromotions = $this->promotions->findActiveByBoutique($boutique);
        $discounts = [];

        foreach ($activePromotions as $promotion) {
            $discount = $this->calculateDiscount($promotion, $cartAmountCents, $productIds, $categoryIds);
            if ($discount > 0) {
                $discounts[] = [
                    'promotionId' => (string) $promotion->getId(),
                    'name' => $promotion->getName(),
                    'discount' => $discount,
                    'priority' => $promotion->getPriority(),
                ];
            }
        }

        usort($discounts, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $discounts;
    }

    private function calculateDiscount(
        Promotion $promotion,
        int $cartAmountCents,
        array $productIds,
        array $categoryIds,
    ): int {
        return match ($promotion->getType()) {
            PromotionType::Percentage => (int) round($cartAmountCents * $promotion->getValue() / 100),
            PromotionType::FixedAmount => min($promotion->getValue(), $cartAmountCents),
        };
    }
}
