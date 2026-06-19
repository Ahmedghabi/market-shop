<?php

namespace App\ApiResource\Coupon;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\Coupon\CouponProvider;
use App\State\Coupon\CouponProcessor;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/coupons/{id}',
            provider: CouponProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/coupons',
            provider: CouponProvider::class,
        ),
        new Post(
            uriTemplate: '/coupons',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: CouponProcessor::class,
        ),
        new Put(
            uriTemplate: '/coupons/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: CouponProcessor::class,
        ),
    ],
)]
final class CouponResource
{
}
