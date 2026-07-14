<?php

namespace App\Service\Delivery;

use App\Entity\DeliveryRule;
use App\Entity\Boutique;
use App\Enum\DeliveryRuleType;
use App\Repository\DeliveryRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DeliveryRuleService
{
    public function __construct(
        private DeliveryRuleRepository $rules,
        private EntityManagerInterface $em,
        private DeliveryCacheService $cache,
    ) {
    }

    public function calculateDeliveryFee(
        string $boutiqueId,
        int $cartAmountCents,
        float $weightKg = 0.0,
        float $distanceKm = 0.0,
    ): int {
        $boutique = $this->em->find(Boutique::class, $boutiqueId);
        if (!$boutique instanceof Boutique) {
            return 0;
        }

        $rules = $this->rules->findActiveByBoutique($boutique);

        foreach ($rules as $rule) {
            if ($rule->matches($weightKg, $distanceKm, $cartAmountCents)) {
                return match ($rule->getType()) {
                    DeliveryRuleType::FreeDelivery => 0,
                    DeliveryRuleType::FixedPrice => $rule->getPriceCents(),
                    DeliveryRuleType::PriceByWeight => $this->calculateByWeight($rule, $weightKg),
                    DeliveryRuleType::PriceByDistance => $this->calculateByDistance($rule, $distanceKm),
                    DeliveryRuleType::PriceByCartAmount => $this->calculateByCartAmount($rule, $cartAmountCents),
                    DeliveryRuleType::ExpressDelivery => $rule->getPriceCents(),
                };
            }
        }

        return 0;
    }

    public function create(string $boutiqueId, array $data): DeliveryRule
    {
        $boutique = $this->em->find(Boutique::class, $boutiqueId);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        $rule = new DeliveryRule(
            boutique: $boutique,
            name: (string) ($data['name'] ?? ''),
            type: DeliveryRuleType::from((string) ($data['type'] ?? 'FIXED_PRICE')),
        );

        if (isset($data['priceCents'])) {
            $rule->setPriceCents((int) $data['priceCents']);
        }
        if (isset($data['minWeightKg'])) {
            $rule->setMinWeightKg((float) $data['minWeightKg']);
        }
        if (isset($data['maxWeightKg'])) {
            $rule->setMaxWeightKg((float) $data['maxWeightKg']);
        }
        if (isset($data['minDistanceKm'])) {
            $rule->setMinDistanceKm((float) $data['minDistanceKm']);
        }
        if (isset($data['maxDistanceKm'])) {
            $rule->setMaxDistanceKm((float) $data['maxDistanceKm']);
        }
        if (isset($data['minCartAmountCents'])) {
            $rule->setMinCartAmountCents((int) $data['minCartAmountCents']);
        }
        if (isset($data['maxCartAmountCents'])) {
            $rule->setMaxCartAmountCents((int) $data['maxCartAmountCents']);
        }
        if (isset($data['priority'])) {
            $rule->setPriority((int) $data['priority']);
        }
        if (isset($data['isActive'])) {
            $rule->setActive((bool) $data['isActive']);
        }

        $this->em->persist($rule);
        $this->em->flush();
        $this->cache->invalidateShop($boutiqueId);

        return $rule;
    }

    public function update(DeliveryRule $rule, array $data): DeliveryRule
    {
        if (isset($data['name'])) {
            $rule->setName((string) $data['name']);
        }
        if (isset($data['type'])) {
            $rule->setType(DeliveryRuleType::from((string) $data['type']));
        }
        if (isset($data['priceCents'])) {
            $rule->setPriceCents((int) $data['priceCents']);
        }
        if (array_key_exists('minWeightKg', $data)) {
            $rule->setMinWeightKg(null !== $data['minWeightKg'] ? (float) $data['minWeightKg'] : null);
        }
        if (array_key_exists('maxWeightKg', $data)) {
            $rule->setMaxWeightKg(null !== $data['maxWeightKg'] ? (float) $data['maxWeightKg'] : null);
        }
        if (array_key_exists('minDistanceKm', $data)) {
            $rule->setMinDistanceKm(null !== $data['minDistanceKm'] ? (float) $data['minDistanceKm'] : null);
        }
        if (array_key_exists('maxDistanceKm', $data)) {
            $rule->setMaxDistanceKm(null !== $data['maxDistanceKm'] ? (float) $data['maxDistanceKm'] : null);
        }
        if (array_key_exists('minCartAmountCents', $data)) {
            $rule->setMinCartAmountCents(null !== $data['minCartAmountCents'] ? (int) $data['minCartAmountCents'] : null);
        }
        if (array_key_exists('maxCartAmountCents', $data)) {
            $rule->setMaxCartAmountCents(null !== $data['maxCartAmountCents'] ? (int) $data['maxCartAmountCents'] : null);
        }
        if (isset($data['priority'])) {
            $rule->setPriority((int) $data['priority']);
        }
        if (isset($data['isActive'])) {
            $rule->setActive((bool) $data['isActive']);
        }

        $this->em->flush();
        $this->cache->invalidateShop((string) $rule->getBoutique()->getId());

        return $rule;
    }

    public function getRulesForBoutique(string $boutiqueId): array
    {
        $boutique = $this->em->find(Boutique::class, $boutiqueId);
        if (!$boutique instanceof Boutique) {
            return [];
        }

        return $this->rules->findByBoutique($boutique);
    }

    public function getRuleById(string $ruleId): ?DeliveryRule
    {
        return $this->rules->find($ruleId);
    }

    private function calculateByWeight(DeliveryRule $rule, float $weightKg): int
    {
        $basePrice = $rule->getPriceCents();
        $minWeight = $rule->getMinWeightKg() ?? 0;

        return (int) round($basePrice * max(1, $weightKg - $minWeight));
    }

    private function calculateByDistance(DeliveryRule $rule, float $distanceKm): int
    {
        $basePrice = $rule->getPriceCents();
        $minDistance = $rule->getMinDistanceKm() ?? 0;

        return (int) round($basePrice * max(1, $distanceKm - $minDistance));
    }

    private function calculateByCartAmount(DeliveryRule $rule, int $cartAmountCents): int
    {
        $basePrice = $rule->getPriceCents();
        $minCart = $rule->getMinCartAmountCents() ?? 0;

        if ($minCart > 0 && $cartAmountCents >= $minCart) {
            return 0;
        }

        return $basePrice;
    }
}
