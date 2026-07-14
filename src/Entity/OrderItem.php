<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_item')]
class OrderItem extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Order $order,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Product $product,
        #[ORM\Column(length: 180)]
        private string $productName,
        #[ORM\Column(length: 80)]
        private string $sku,
        #[ORM\Column]
        private int $quantity,
        #[ORM\Column]
        private int $unitPriceCents,
        #[ORM\Column]
        private int $discountCents = 0,
        #[ORM\Column]
        private int $totalCents = 0,
    ) {
        parent::__construct();
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }
}
