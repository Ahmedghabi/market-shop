<?php

namespace App\Entity;

use App\Enum\LoyaltyValidityPolicy;
use App\Repository\LoyaltyProgramRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Per-boutique loyalty program configuration. LoyaltyEngine is the only service
 * allowed to interpret these settings to compute points and rewards.
 */
#[ORM\Entity(repositoryClass: LoyaltyProgramRepository::class)]
#[ORM\Table(name: 'loyalty_program')]
#[ORM\UniqueConstraint(name: 'uniq_loyalty_program_boutique', columns: ['boutique_id'])]
class LoyaltyProgram extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column]
        private bool $isActive = false,
        #[ORM\Column(length: 32, enumType: LoyaltyValidityPolicy::class)]
        private LoyaltyValidityPolicy $pointsValidityPolicy = LoyaltyValidityPolicy::Never,
        #[ORM\Column(nullable: true)]
        private ?int $customValidityDays = null,
        #[ORM\Column]
        private bool $allowChooseAmount = true,
        #[ORM\Column]
        private bool $allowUseAllPoints = true,
        #[ORM\Column]
        private bool $allowRewardSelection = true,
        #[ORM\Column]
        private int $minPointsToRedeem = 0,
        #[ORM\Column]
        private int $pointValueCents = 1,
        #[ORM\Column(nullable: true)]
        private ?int $maxPointsPerOrder = null,
        #[ORM\Column(nullable: true)]
        private ?int $maxDiscountCentsPerOrder = null,
        #[ORM\Column]
        private int $minOrderAmountCentsToRedeem = 0,
        #[ORM\Column]
        private int $minOrdersCountToRedeem = 0,
        #[ORM\Column]
        private bool $combinableWithPromotions = true,
        #[ORM\Column]
        private bool $combinableWithCoupons = true,
        #[ORM\Column]
        private bool $combinableWithOtherDiscounts = true,
        #[ORM\Column]
        private bool $combinableWithFreeShipping = true,
        #[ORM\Column]
        private bool $returnUsedPointsOnCancel = true,
        #[ORM\Column]
        private bool $revokeEarnedPointsOnCancel = true,
        #[ORM\Column(type: 'json')]
        private array $calculationOrder = ['subtotal', 'promotions', 'coupons', 'loyalty', 'delivery', 'taxes', 'total'],
        #[ORM\Column]
        private int $cacheTtlSeconds = 600,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function getPointsValidityPolicy(): LoyaltyValidityPolicy
    {
        return $this->pointsValidityPolicy;
    }

    public function setPointsValidityPolicy(LoyaltyValidityPolicy $policy): void
    {
        $this->pointsValidityPolicy = $policy;
        $this->touch();
    }

    public function getCustomValidityDays(): ?int
    {
        return $this->customValidityDays;
    }

    public function setCustomValidityDays(?int $days): void
    {
        $this->customValidityDays = $days;
        $this->touch();
    }

    /**
     * Effective number of days points remain valid, or null when they never expire.
     */
    public function getValidityDays(): ?int
    {
        if (LoyaltyValidityPolicy::Custom === $this->pointsValidityPolicy) {
            return $this->customValidityDays;
        }

        return $this->pointsValidityPolicy->days();
    }

    public function isAllowChooseAmount(): bool
    {
        return $this->allowChooseAmount;
    }

    public function setAllowChooseAmount(bool $allow): void
    {
        $this->allowChooseAmount = $allow;
        $this->touch();
    }

    public function isAllowUseAllPoints(): bool
    {
        return $this->allowUseAllPoints;
    }

    public function setAllowUseAllPoints(bool $allow): void
    {
        $this->allowUseAllPoints = $allow;
        $this->touch();
    }

    public function isAllowRewardSelection(): bool
    {
        return $this->allowRewardSelection;
    }

    public function setAllowRewardSelection(bool $allow): void
    {
        $this->allowRewardSelection = $allow;
        $this->touch();
    }

    public function getMinPointsToRedeem(): int
    {
        return $this->minPointsToRedeem;
    }

    public function setMinPointsToRedeem(int $min): void
    {
        $this->minPointsToRedeem = $min;
        $this->touch();
    }

    /**
     * Value in cents of a single point when redeemed generically (no specific
     * reward selected) — e.g. "use 200 points" -> 200 * pointValueCents.
     */
    public function getPointValueCents(): int
    {
        return $this->pointValueCents;
    }

    public function setPointValueCents(int $value): void
    {
        $this->pointValueCents = $value;
        $this->touch();
    }

    public function getMaxPointsPerOrder(): ?int
    {
        return $this->maxPointsPerOrder;
    }

    public function setMaxPointsPerOrder(?int $max): void
    {
        $this->maxPointsPerOrder = $max;
        $this->touch();
    }

    public function getMaxDiscountCentsPerOrder(): ?int
    {
        return $this->maxDiscountCentsPerOrder;
    }

    public function setMaxDiscountCentsPerOrder(?int $max): void
    {
        $this->maxDiscountCentsPerOrder = $max;
        $this->touch();
    }

    public function getMinOrderAmountCentsToRedeem(): int
    {
        return $this->minOrderAmountCentsToRedeem;
    }

    public function setMinOrderAmountCentsToRedeem(int $min): void
    {
        $this->minOrderAmountCentsToRedeem = $min;
        $this->touch();
    }

    public function getMinOrdersCountToRedeem(): int
    {
        return $this->minOrdersCountToRedeem;
    }

    public function setMinOrdersCountToRedeem(int $min): void
    {
        $this->minOrdersCountToRedeem = $min;
        $this->touch();
    }

    public function isCombinableWithPromotions(): bool
    {
        return $this->combinableWithPromotions;
    }

    public function setCombinableWithPromotions(bool $value): void
    {
        $this->combinableWithPromotions = $value;
        $this->touch();
    }

    public function isCombinableWithCoupons(): bool
    {
        return $this->combinableWithCoupons;
    }

    public function setCombinableWithCoupons(bool $value): void
    {
        $this->combinableWithCoupons = $value;
        $this->touch();
    }

    public function isCombinableWithOtherDiscounts(): bool
    {
        return $this->combinableWithOtherDiscounts;
    }

    public function setCombinableWithOtherDiscounts(bool $value): void
    {
        $this->combinableWithOtherDiscounts = $value;
        $this->touch();
    }

    public function isCombinableWithFreeShipping(): bool
    {
        return $this->combinableWithFreeShipping;
    }

    public function setCombinableWithFreeShipping(bool $value): void
    {
        $this->combinableWithFreeShipping = $value;
        $this->touch();
    }

    public function isReturnUsedPointsOnCancel(): bool
    {
        return $this->returnUsedPointsOnCancel;
    }

    public function setReturnUsedPointsOnCancel(bool $value): void
    {
        $this->returnUsedPointsOnCancel = $value;
        $this->touch();
    }

    public function isRevokeEarnedPointsOnCancel(): bool
    {
        return $this->revokeEarnedPointsOnCancel;
    }

    public function setRevokeEarnedPointsOnCancel(bool $value): void
    {
        $this->revokeEarnedPointsOnCancel = $value;
        $this->touch();
    }

    /** @return list<string> */
    public function getCalculationOrder(): array
    {
        return $this->calculationOrder;
    }

    /** @param list<string> $order */
    public function setCalculationOrder(array $order): void
    {
        $this->calculationOrder = $order;
        $this->touch();
    }

    public function getCacheTtlSeconds(): int
    {
        return $this->cacheTtlSeconds;
    }

    public function setCacheTtlSeconds(int $ttl): void
    {
        $this->cacheTtlSeconds = $ttl;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
