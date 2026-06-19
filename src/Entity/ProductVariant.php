<?php

namespace App\Entity;

use App\Repository\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[ORM\Table(name: 'product_variant')]
class ProductVariant extends AbstractEntity
{
    /** @var Collection<int, ProductVariantAttribute> */
    #[ORM\OneToMany(mappedBy: 'variant', targetEntity: ProductVariantAttribute::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $attributes;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'variants')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column(length: 80, nullable: true)]
        private ?string $sku = null,
        #[ORM\Column(length: 80, nullable: true)]
        private ?string $barcode = null,
        #[ORM\Column]
        private int $sellingPrice = 0,
        #[ORM\Column]
        private int $comparePrice = 0,
        #[ORM\Column]
        private int $quantity = 0,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $image = null,
        #[ORM\Column]
        private bool $isDefault = false,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->attributes = new ArrayCollection();
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
        $this->touch();
    }

    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    public function setBarcode(?string $barcode): void
    {
        $this->barcode = $barcode;
        $this->touch();
    }

    public function getSellingPrice(): int
    {
        return $this->sellingPrice;
    }

    public function setSellingPrice(int $price): void
    {
        $this->sellingPrice = $price;
        $this->touch();
    }

    public function getComparePrice(): int
    {
        return $this->comparePrice;
    }

    public function setComparePrice(int $price): void
    {
        $this->comparePrice = $price;
        $this->touch();
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
        $this->touch();
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
        $this->touch();
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $default): void
    {
        $this->isDefault = $default;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $active): void
    {
        $this->isActive = $active;
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

    /** @return Collection<int, ProductVariantAttribute> */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(ProductVariantAttribute $attr): void
    {
        if (!$this->attributes->contains($attr)) {
            $this->attributes->add($attr);
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
