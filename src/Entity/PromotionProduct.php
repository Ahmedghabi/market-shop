<?php

namespace App\Entity;

use App\Repository\PromotionProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionProductRepository::class)]
#[ORM\Table(name: 'promotion_product')]
#[ORM\UniqueConstraint(name: 'uniq_promotion_product', columns: ['promotion_id', 'product_id'])]
class PromotionProduct extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: 'products')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Promotion $promotion,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
    ) {
        parent::__construct();
    }
}
