<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CouponCategoryRepository::class)]
#[ORM\Table(name: 'coupon_category')]
class CouponCategory extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'categories')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Coupon $coupon,
        #[ORM\ManyToOne(targetEntity: Category::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Category $category,
    ) {
        parent::__construct();
    }

    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }
}
