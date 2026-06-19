<?php

namespace App\Entity;

use App\Enum\StockMovementType;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockMovementRepository::class)]
#[ORM\Table(name: 'stock_movement')]
class StockMovement extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\ManyToOne(targetEntity: ProductVariant::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?ProductVariant $variant = null,
        #[ORM\Column(length: 32, enumType: StockMovementType::class)]
        private StockMovementType $type = StockMovementType::Adjustment,
        #[ORM\Column]
        private int $quantity = 0,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $reason = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getVariant(): ?ProductVariant
    {
        return $this->variant;
    }

    public function getType(): StockMovementType
    {
        return $this->type;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
