<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Enum\PromotionScope;
use App\Enum\PromotionType;
use App\Repository\PromotionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
#[ORM\Table(name: 'promotion')]
class Promotion extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, PromotionCategory> */
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionCategory::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $categories;

    /** @var Collection<int, PromotionProduct> */
    #[ORM\OneToMany(mappedBy: 'promotion', targetEntity: PromotionProduct::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $products;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 32, enumType: PromotionScope::class)]
        private PromotionScope $scope,
        #[ORM\Column(length: 32, enumType: PromotionType::class)]
        private PromotionType $type,
        #[ORM\Column]
        private int $value,
        #[ORM\Column]
        private \DateTimeImmutable $startsAt,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column]
        private int $priority = 0,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $endsAt = null,
        #[ORM\Column]
        private bool $active = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->categories = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function priority(): int
    {
        return $this->priority > 0 ? $this->priority : match ($this->scope) {
            PromotionScope::Product => 300,
            PromotionScope::Category => 200,
            PromotionScope::Global => 100,
        };
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
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

    public function getScope(): PromotionScope
    {
        return $this->scope;
    }

    public function setScope(PromotionScope $scope): void
    {
        $this->scope = $scope;
        $this->touch();
    }

    public function getType(): PromotionType
    {
        return $this->type;
    }

    public function setType(PromotionType $type): void
    {
        $this->type = $type;
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

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
        $this->touch();
    }

    public function getStartsAt(): \DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): void
    {
        $this->startsAt = $startsAt;
        $this->touch();
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): void
    {
        $this->endsAt = $endsAt;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
        $this->touch();
    }

    /** @return Collection<int, PromotionCategory> */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(PromotionCategory $promotionCategory): void
    {
        if (!$this->categories->contains($promotionCategory)) {
            $this->categories->add($promotionCategory);
            $this->touch();
        }
    }

    public function removeCategory(PromotionCategory $promotionCategory): void
    {
        if ($this->categories->removeElement($promotionCategory)) {
            $this->touch();
        }
    }

    /** @return Collection<int, PromotionProduct> */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(PromotionProduct $promotionProduct): void
    {
        if (!$this->products->contains($promotionProduct)) {
            $this->products->add($promotionProduct);
            $this->touch();
        }
    }

    public function removeProduct(PromotionProduct $promotionProduct): void
    {
        if ($this->products->removeElement($promotionProduct)) {
            $this->touch();
        }
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $now = new \DateTimeImmutable();

        if ($now < $this->startsAt) {
            return false;
        }

        return null === $this->endsAt || $now <= $this->endsAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
