<?php

namespace App\Entity;

use App\Enum\DeliveryRuleType;
use App\Repository\DeliveryRuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryRuleRepository::class)]
#[ORM\Table(name: 'delivery_rule')]
class DeliveryRule extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 120)]
        private string $name,
        #[ORM\Column(length: 32, enumType: DeliveryRuleType::class)]
        private DeliveryRuleType $type,
        #[ORM\Column]
        private int $priceCents = 0,
        #[ORM\Column(nullable: true)]
        private ?float $minWeightKg = null,
        #[ORM\Column(nullable: true)]
        private ?float $maxWeightKg = null,
        #[ORM\Column(nullable: true)]
        private ?float $minDistanceKm = null,
        #[ORM\Column(nullable: true)]
        private ?float $maxDistanceKm = null,
        #[ORM\Column(nullable: true)]
        private ?int $minCartAmountCents = null,
        #[ORM\Column(nullable: true)]
        private ?int $maxCartAmountCents = null,
        #[ORM\Column]
        private int $priority = 0,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private ?\DateTimeImmutable $createdAt = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->createdAt ??= new \DateTimeImmutable();
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

    public function getType(): DeliveryRuleType
    {
        return $this->type;
    }

    public function setType(DeliveryRuleType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }

    public function setPriceCents(int $priceCents): void
    {
        $this->priceCents = $priceCents;
        $this->touch();
    }

    public function getMinWeightKg(): ?float
    {
        return $this->minWeightKg;
    }

    public function setMinWeightKg(?float $minWeightKg): void
    {
        $this->minWeightKg = $minWeightKg;
        $this->touch();
    }

    public function getMaxWeightKg(): ?float
    {
        return $this->maxWeightKg;
    }

    public function setMaxWeightKg(?float $maxWeightKg): void
    {
        $this->maxWeightKg = $maxWeightKg;
        $this->touch();
    }

    public function getMinDistanceKm(): ?float
    {
        return $this->minDistanceKm;
    }

    public function setMinDistanceKm(?float $minDistanceKm): void
    {
        $this->minDistanceKm = $minDistanceKm;
        $this->touch();
    }

    public function getMaxDistanceKm(): ?float
    {
        return $this->maxDistanceKm;
    }

    public function setMaxDistanceKm(?float $maxDistanceKm): void
    {
        $this->maxDistanceKm = $maxDistanceKm;
        $this->touch();
    }

    public function getMinCartAmountCents(): ?int
    {
        return $this->minCartAmountCents;
    }

    public function setMinCartAmountCents(?int $minCartAmountCents): void
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

    public function matches(float $weightKg, float $distanceKm, int $cartAmountCents): bool
    {
        if (null !== $this->minWeightKg && $weightKg < $this->minWeightKg) {
            return false;
        }
        if (null !== $this->maxWeightKg && $weightKg > $this->maxWeightKg) {
            return false;
        }
        if (null !== $this->minDistanceKm && $distanceKm < $this->minDistanceKm) {
            return false;
        }
        if (null !== $this->maxDistanceKm && $distanceKm > $this->maxDistanceKm) {
            return false;
        }
        if (null !== $this->minCartAmountCents && $cartAmountCents < $this->minCartAmountCents) {
            return false;
        }
        if (null !== $this->maxCartAmountCents && $cartAmountCents > $this->maxCartAmountCents) {
            return false;
        }

        return true;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
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
