<?php

namespace App\Entity;

use App\Repository\ProductStockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductStockRepository::class)]
#[ORM\Table(name: 'product_stock')]
class ProductStock extends AbstractEntity
{
    public function __construct(
        #[ORM\OneToOne(inversedBy: 'stock', targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column]
        private int $quantity = 0,
        #[ORM\Column]
        private int $reservedQuantity = 0,
        #[ORM\Column]
        private int $lowStockThreshold = 0,
    ) {
        parent::__construct();
        $product->setStock($this);
    }

    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reservedQuantity);
    }

    public function getLowStockThreshold(): int
    {
        return $this->lowStockThreshold;
    }

    public function update(int $quantity, int $lowStockThreshold): void
    {
        $this->quantity = $quantity;
        $this->lowStockThreshold = $lowStockThreshold;
    }
}
