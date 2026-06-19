<?php

namespace App\Service\Marketing;

use App\Repository\BoutiqueRepository;
use App\Repository\PromotionRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class MarketingCacheService
{
    private const int TTL = 21600;

    public function __construct(
        private CacheInterface $cache,
        private BoutiqueRepository $boutiques,
        private PromotionRepository $promotions,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function getPromotions(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.promotions", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);
            $boutique = $this->boutiques->find($boutiqueId);
            if (!$boutique) {
                return [];
            }

            return array_map(fn ($promotion) => [
                'id' => (string) $promotion->getId(),
                'boutiqueId' => (string) $promotion->getBoutique()->getId(),
                'name' => $promotion->getName(),
                'description' => $promotion->getDescription(),
                'scope' => $promotion->getScope()->value,
                'type' => $promotion->getType()->value,
                'value' => $promotion->getValue(),
                'priority' => $promotion->getPriority(),
                'categoryIds' => array_map(fn ($pc) => (string) $pc->getCategory()->getId(), $promotion->getCategories()->toArray()),
                'productIds' => array_map(fn ($pp) => (string) $pp->getProduct()->getId(), $promotion->getProducts()->toArray()),
                'startsAt' => $promotion->getStartsAt()->format('c'),
                'endsAt' => $promotion->getEndsAt()?->format('c'),
                'active' => $promotion->isActive(),
                'currentlyActive' => $promotion->isCurrentlyActive(),
            ], $this->promotions->findActiveByBoutique($boutique));
        });
    }

    public function invalidatePromotions(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.promotions");
    }

    public function invalidateCoupons(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.coupons");
    }

    public function invalidateDeliveryRules(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.delivery_rules");
    }
}
