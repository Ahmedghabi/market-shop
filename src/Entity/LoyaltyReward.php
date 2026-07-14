<?php

namespace App\Entity;

use App\Enum\LoyaltyCostType;
use App\Repository\LoyaltyRewardRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A configurable redeemable reward. `typeCode` is validated against the
 * LoyaltyRewardApplierRegistry rather than a closed PHP enum, so new reward
 * types can be added (new applier class) without ever touching this entity.
 */
#[ORM\Entity(repositoryClass: LoyaltyRewardRepository::class)]
#[ORM\Table(name: 'loyalty_reward')]
#[ORM\Index(name: 'idx_loyalty_reward_program', columns: ['program_id'])]
class LoyaltyReward extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: LoyaltyProgram::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private LoyaltyProgram $program,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 64)]
        private string $typeCode = 'fixed_discount',
        #[ORM\Column(type: 'json')]
        private array $config = [],
        #[ORM\Column(length: 32, enumType: LoyaltyCostType::class)]
        private LoyaltyCostType $costType = LoyaltyCostType::Points,
        #[ORM\Column]
        private int $costValue = 0,
        #[ORM\Column(nullable: true)]
        private ?int $minOrderAmountCents = null,
        #[ORM\Column(nullable: true)]
        private ?int $maxDiscountCents = null,
        #[ORM\Column(nullable: true)]
        private ?int $minOrdersRequired = null,
        #[ORM\Column(nullable: true)]
        private ?int $validityDays = null,
        #[ORM\Column(nullable: true)]
        private ?bool $combinableWithPromotions = null,
        #[ORM\Column(nullable: true)]
        private ?bool $combinableWithCoupons = null,
        #[ORM\Column(nullable: true)]
        private ?bool $combinableWithOtherDiscounts = null,
        #[ORM\Column(nullable: true)]
        private ?bool $combinableWithFreeShipping = null,
        #[ORM\Column(nullable: true)]
        private ?int $usageLimit = null,
        #[ORM\Column(nullable: true)]
        private ?int $usageLimitPerCustomer = null,
        #[ORM\Column]
        private int $priority = 0,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getProgram(): LoyaltyProgram
    {
        return $this->program;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function getTypeCode(): string
    {
        return $this->typeCode;
    }

    public function setTypeCode(string $typeCode): void
    {
        $this->typeCode = $typeCode;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getConfig(): array
    {
        return $this->config;
    }

    /** @param array<string, mixed> $config */
    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->touch();
    }

    public function getCostType(): LoyaltyCostType
    {
        return $this->costType;
    }

    public function setCostType(LoyaltyCostType $costType): void
    {
        $this->costType = $costType;
        $this->touch();
    }

    public function getCostValue(): int
    {
        return $this->costValue;
    }

    public function setCostValue(int $costValue): void
    {
        $this->costValue = $costValue;
        $this->touch();
    }

    public function getMinOrderAmountCents(): ?int
    {
        return $this->minOrderAmountCents;
    }

    public function setMinOrderAmountCents(?int $min): void
    {
        $this->minOrderAmountCents = $min;
        $this->touch();
    }

    public function getMaxDiscountCents(): ?int
    {
        return $this->maxDiscountCents;
    }

    public function setMaxDiscountCents(?int $max): void
    {
        $this->maxDiscountCents = $max;
        $this->touch();
    }

    public function getMinOrdersRequired(): ?int
    {
        return $this->minOrdersRequired;
    }

    public function setMinOrdersRequired(?int $min): void
    {
        $this->minOrdersRequired = $min;
        $this->touch();
    }

    public function getValidityDays(): ?int
    {
        return $this->validityDays;
    }

    public function setValidityDays(?int $days): void
    {
        $this->validityDays = $days;
        $this->touch();
    }

    public function getCombinableWithPromotions(): ?bool
    {
        return $this->combinableWithPromotions;
    }

    public function setCombinableWithPromotions(?bool $value): void
    {
        $this->combinableWithPromotions = $value;
        $this->touch();
    }

    public function getCombinableWithCoupons(): ?bool
    {
        return $this->combinableWithCoupons;
    }

    public function setCombinableWithCoupons(?bool $value): void
    {
        $this->combinableWithCoupons = $value;
        $this->touch();
    }

    public function getCombinableWithOtherDiscounts(): ?bool
    {
        return $this->combinableWithOtherDiscounts;
    }

    public function setCombinableWithOtherDiscounts(?bool $value): void
    {
        $this->combinableWithOtherDiscounts = $value;
        $this->touch();
    }

    public function getCombinableWithFreeShipping(): ?bool
    {
        return $this->combinableWithFreeShipping;
    }

    public function setCombinableWithFreeShipping(?bool $value): void
    {
        $this->combinableWithFreeShipping = $value;
        $this->touch();
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(?int $limit): void
    {
        $this->usageLimit = $limit;
        $this->touch();
    }

    public function getUsageLimitPerCustomer(): ?int
    {
        return $this->usageLimitPerCustomer;
    }

    public function setUsageLimitPerCustomer(?int $limit): void
    {
        $this->usageLimitPerCustomer = $limit;
        $this->touch();
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
        $this->touch();
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
