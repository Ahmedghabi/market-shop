<?php

namespace App\Entity;

use App\Repository\InvoiceItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceItemRepository::class)]
#[ORM\Table(name: 'invoice_item')]
class InvoiceItem extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Invoice $invoice,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Product $product,
        #[ORM\Column(length: 255)]
        private string $description,
        #[ORM\Column]
        private int $quantity,
        #[ORM\Column]
        private int $unitPrice,
        #[ORM\Column]
        private int $discount = 0,
        #[ORM\Column]
        private int $tax = 0,
        #[ORM\Column]
        private int $total = 0,
    ) {
        parent::__construct();
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): int
    {
        return $this->unitPrice;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}
