<?php

namespace App\Entity;

use App\Repository\PromotionCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromotionCategoryRepository::class)]
#[ORM\Table(name: 'promotion_category')]
#[ORM\UniqueConstraint(name: 'uniq_promotion_category', columns: ['promotion_id', 'category_id'])]
class PromotionCategory extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Promotion::class, inversedBy: 'categories')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Promotion $promotion,
        #[ORM\ManyToOne(targetEntity: Category::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Category $category,
    ) {
        parent::__construct();
    }
}
