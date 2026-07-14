<?php

namespace App\Service\Loyalty;

use App\Entity\LoyaltyProgram;
use App\Entity\LoyaltyReward;
use App\Entity\LoyaltyRule;
use App\Repository\BoutiqueRepository;
use App\Repository\LoyaltyProgramRepository;
use App\Repository\LoyaltyRewardRepository;
use App\Repository\LoyaltyRuleRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Caches program config, rules and rewards for read-heavy endpoints
 * (customer space, checkout quote). Invalidated explicitly by the
 * Program/Rule/Reward processors right after every write — no Doctrine
 * listener needed, matching the Promotion/Coupon/DeliveryRule pattern.
 */
final readonly class LoyaltyCacheService
{
    private const int DEFAULT_TTL = 600;

    public function __construct(
        private CacheInterface $cache,
        private LoyaltyProgramRepository $programs,
        private LoyaltyRuleRepository $rules,
        private LoyaltyRewardRepository $rewards,
        private BoutiqueRepository $boutiques,
    ) {
    }

    /** @return array<string, mixed>|null */
    public function getProgram(string $boutiqueId): ?array
    {
        return $this->cache->get("shop.{$boutiqueId}.loyalty.program", function (ItemInterface $item) use ($boutiqueId): ?array {
            $item->expiresAfter(self::DEFAULT_TTL);
            $program = $this->findByBoutiqueId($boutiqueId);

            return null !== $program ? $this->programToArray($program) : null;
        });
    }

    /** @return list<array<string, mixed>> */
    public function getRules(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.loyalty.rules", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::DEFAULT_TTL);
            $program = $this->findByBoutiqueId($boutiqueId);
            if (null === $program) {
                return [];
            }

            return array_map($this->ruleToArray(...), $this->rules->findByProgram($program));
        });
    }

    /** @return list<array<string, mixed>> */
    public function getRewards(string $boutiqueId): array
    {
        return $this->cache->get("shop.{$boutiqueId}.loyalty.rewards", function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::DEFAULT_TTL);
            $program = $this->findByBoutiqueId($boutiqueId);
            if (null === $program) {
                return [];
            }

            return array_map($this->rewardToArray(...), $this->rewards->findByProgram($program));
        });
    }

    public function invalidateProgram(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.loyalty.program");
    }

    public function invalidateRules(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.loyalty.rules");
    }

    public function invalidateRewards(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.loyalty.rewards");
    }

    public function invalidateAll(string $boutiqueId): void
    {
        $this->invalidateProgram($boutiqueId);
        $this->invalidateRules($boutiqueId);
        $this->invalidateRewards($boutiqueId);
    }

    private function findByBoutiqueId(string $boutiqueId): ?LoyaltyProgram
    {
        $boutique = $this->boutiques->find($boutiqueId);
        if (null === $boutique) {
            return null;
        }

        return $this->programs->findOneByBoutique($boutique);
    }

    /** @return array<string, mixed> */
    private function programToArray(LoyaltyProgram $program): array
    {
        return [
            'id' => (string) $program->getId(),
            'boutiqueId' => (string) $program->getBoutique()->getId(),
            'isActive' => $program->isActive(),
            'pointsValidityPolicy' => $program->getPointsValidityPolicy()->value,
            'customValidityDays' => $program->getCustomValidityDays(),
            'pointValueCents' => $program->getPointValueCents(),
            'allowChooseAmount' => $program->isAllowChooseAmount(),
            'allowUseAllPoints' => $program->isAllowUseAllPoints(),
            'allowRewardSelection' => $program->isAllowRewardSelection(),
            'minPointsToRedeem' => $program->getMinPointsToRedeem(),
            'maxPointsPerOrder' => $program->getMaxPointsPerOrder(),
            'maxDiscountCentsPerOrder' => $program->getMaxDiscountCentsPerOrder(),
            'minOrderAmountCentsToRedeem' => $program->getMinOrderAmountCentsToRedeem(),
            'minOrdersCountToRedeem' => $program->getMinOrdersCountToRedeem(),
            'combinableWithPromotions' => $program->isCombinableWithPromotions(),
            'combinableWithCoupons' => $program->isCombinableWithCoupons(),
            'combinableWithOtherDiscounts' => $program->isCombinableWithOtherDiscounts(),
            'combinableWithFreeShipping' => $program->isCombinableWithFreeShipping(),
            'calculationOrder' => $program->getCalculationOrder(),
        ];
    }

    /** @return array<string, mixed> */
    private function ruleToArray(LoyaltyRule $rule): array
    {
        return [
            'id' => (string) $rule->getId(),
            'name' => $rule->getName(),
            'description' => $rule->getDescription(),
            'triggerCode' => $rule->getTriggerCode(),
            'triggerConfig' => $rule->getTriggerConfig(),
            'rewardPoints' => $rule->getRewardPoints(),
            'isMultiplier' => $rule->isMultiplier(),
            'multiplierValue' => $rule->getMultiplierValue(),
            'priority' => $rule->getPriority(),
            'isActive' => $rule->isActive(),
            'isCumulative' => $rule->isCumulative(),
            'startsAt' => $rule->getStartsAt()?->format('c'),
            'endsAt' => $rule->getEndsAt()?->format('c'),
        ];
    }

    /** @return array<string, mixed> */
    private function rewardToArray(LoyaltyReward $reward): array
    {
        return [
            'id' => (string) $reward->getId(),
            'name' => $reward->getName(),
            'description' => $reward->getDescription(),
            'typeCode' => $reward->getTypeCode(),
            'config' => $reward->getConfig(),
            'costType' => $reward->getCostType()->value,
            'costValue' => $reward->getCostValue(),
            'minOrderAmountCents' => $reward->getMinOrderAmountCents(),
            'maxDiscountCents' => $reward->getMaxDiscountCents(),
            'minOrdersRequired' => $reward->getMinOrdersRequired(),
            'validityDays' => $reward->getValidityDays(),
            'isActive' => $reward->isActive(),
            'priority' => $reward->getPriority(),
        ];
    }
}
