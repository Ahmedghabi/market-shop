<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\CouponProductRepository::class)]
#[ORM\Table(name: 'coupon_product')]
class CouponProduct extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Coupon::class, inversedBy: 'products')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Coupon $coupon,
        #[ORM\ManyToOne(targetEntity: Product::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Product $product,
    ) {
        parent::__construct();
    }

    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }
}
