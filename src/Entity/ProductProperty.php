<?php

namespace App\Entity;

use App\Repository\ProductPropertyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductPropertyRepository::class)]
#[ORM\Table(name: 'product_property')]
class ProductProperty extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'properties')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\Column(length: 80)]
        private string $name,
        #[ORM\Column(length: 255)]
        private string $value,
    ) {
        parent::__construct();
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
