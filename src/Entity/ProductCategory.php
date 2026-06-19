<?php

namespace App\Entity;

use App\Repository\ProductCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductCategoryRepository::class)]
#[ORM\Table(name: 'product_category')]
#[ORM\UniqueConstraint(name: 'uniq_product_category', columns: ['product_id', 'category_id'])]
class ProductCategory extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'productCategories')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
        #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'productCategories')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Category $category,
    ) {
        parent::__construct();
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
