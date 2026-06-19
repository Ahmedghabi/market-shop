<?php

namespace App\Entity;

use App\Repository\ProductVariantAttributeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductVariantAttributeRepository::class)]
#[ORM\Table(name: 'product_variant_attribute')]
class ProductVariantAttribute extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: ProductVariant::class, inversedBy: 'attributes')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ProductVariant $variant,
        #[ORM\Column(length: 80)]
        private string $attributeName,
        #[ORM\Column(length: 120)]
        private string $attributeValue,
    ) {
        parent::__construct();
    }

    public function getVariant(): ProductVariant
    {
        return $this->variant;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function setAttributeName(string $name): void
    {
        $this->attributeName = $name;
    }

    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }

    public function setAttributeValue(string $value): void
    {
        $this->attributeValue = $value;
    }
}
