<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Enum\CouponScope;
use App\Enum\CouponType;
use App\Repository\CouponRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupon')]
#[ORM\UniqueConstraint(name: 'uniq_coupon_code_boutique', columns: ['boutique_id', 'code'])]
class Coupon extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, CouponProduct> */
    #[ORM\OneToMany(mappedBy: 'coupon', targetEntity: CouponProduct::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $products;

    /** @var Collection<int, CouponCategory> */
    #[ORM\OneToMany(mappedBy: 'coupon', targetEntity: CouponCategory::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $categories;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 64)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 32, enumType: CouponType::class)]
        private CouponType $type,
        #[ORM\Column(length: 32, enumType: CouponScope::class)]
        private CouponScope $scope = CouponScope::Global,
        #[ORM\Column]
        private int $value = 0,
        #[ORM\Column(nullable: true)]
        private ?int $maxDiscountCents = null,
        #[ORM\Column]
        private int $minCartAmountCents = 0,
        #[ORM\Column(nullable: true)]
        private ?int $maxCartAmountCents = null,
        #[ORM\Column]
        private int $usageLimit = 0,
        #[ORM\Column]
        private int $usedCount = 0,
        #[ORM\Column(nullable: true)]
        private ?int $perUserLimit = null,
        #[ORM\Column]
        private bool $combineWithPromotions = false,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $startsAt = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $expiresAt = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $buyXGetYConfig = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->products = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeUpper(): string
    {
        return strtoupper($this->code);
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

    public function getType(): CouponType
    {
        return $this->type;
    }

    public function setType(CouponType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getScope(): CouponScope
    {
        return $this->scope;
    }

    public function setScope(CouponScope $scope): void
    {
        $this->scope = $scope;
        $this->touch();
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
        $this->touch();
    }

    public function getMaxDiscountCents(): ?int
    {
        return $this->maxDiscountCents;
    }

    public function setMaxDiscountCents(?int $maxDiscountCents): void
    {
        $this->maxDiscountCents = $maxDiscountCents;
        $this->touch();
    }

    public function getMinCartAmountCents(): int
    {
        return $this->minCartAmountCents;
    }

    public function setMinCartAmountCents(int $minCartAmountCents): void
    {
        $this->minCartAmountCents = $minCartAmountCents;
        $this->touch();
    }

    public function getMaxCartAmountCents(): ?int
    {
        return $this->maxCartAmountCents;
    }

    public function setMaxCartAmountCents(?int $maxCartAmountCents): void
    {
        $this->maxCartAmountCents = $maxCartAmountCents;
        $this->touch();
    }

    public function getUsageLimit(): int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(int $usageLimit): void
    {
        $this->usageLimit = $usageLimit;
        $this->touch();
    }

    public function getUsedCount(): int
    {
        return $this->usedCount;
    }

    public function incrementUsedCount(): void
    {
        ++$this->usedCount;
        $this->touch();
    }

    public function getPerUserLimit(): ?int
    {
        return $this->perUserLimit;
    }

    public function setPerUserLimit(?int $perUserLimit): void
    {
        $this->perUserLimit = $perUserLimit;
        $this->touch();
    }

    public function isCombineWithPromotions(): bool
    {
        return $this->combineWithPromotions;
    }

    public function setCombineWithPromotions(bool $combineWithPromotions): void
    {
        $this->combineWithPromotions = $combineWithPromotions;
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

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): void
    {
        $this->startsAt = $startsAt;
        $this->touch();
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
        $this->touch();
    }

    public function getBuyXGetYConfig(): ?array
    {
        return $this->buyXGetYConfig;
    }

    public function setBuyXGetYConfig(?array $config): void
    {
        $this->buyXGetYConfig = $config;
        $this->touch();
    }

    /** @return Collection<int, CouponProduct> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(CouponProduct $product): void
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $this->touch();
        }
    }

    /** @return Collection<int, CouponCategory> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(CouponCategory $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $this->touch();
        }
    }

    public function isCurrentlyValid(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        $now = new \DateTimeImmutable();

        if (null !== $this->startsAt && $now < $this->startsAt) {
            return false;
        }

        if (null !== $this->expiresAt && $now > $this->expiresAt) {
            return false;
        }

        if ($this->usageLimit > 0 && $this->usedCount >= $this->usageLimit) {
            return false;
        }

        return true;
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
