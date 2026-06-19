<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
#[ORM\Table(name: 'cart_item')]
#[ORM\UniqueConstraint(name: 'uniq_cart_item_product', columns: ['cart_id', 'product_id'])]
class CartItem extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Cart $cart,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Product $product,
        #[ORM\Column]
        private int $quantity,
        #[ORM\Column]
        private int $unitPriceCents,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column]
        private \DateTimeImmutable $updatedAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
        $this->quantity = max(1, $quantity);
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function changeQuantity(int $quantity): void
    {
        $this->quantity = max(1, $quantity);
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getUnitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function getTotalCents(): int
    {
        return $this->unitPriceCents * $this->quantity;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
