<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\RefundItemRepository::class)]
#[ORM\Table(name: 'refund_item')]
class RefundItem extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Refund::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Refund $refund,
        #[ORM\ManyToOne(targetEntity: OrderItem::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?OrderItem $orderItem,
        #[ORM\Column(length: 180)]
        private string $productName,
        #[ORM\Column]
        private int $quantity,
        #[ORM\Column]
        private int $unitPriceCents,
        #[ORM\Column]
        private int $totalCents,
    ) {
        parent::__construct();
    }

    public function getRefund(): Refund
    {
        return $this->refund;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
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

    public function getTotalCents(): int
    {
        return $this->totalCents;
    }
}
