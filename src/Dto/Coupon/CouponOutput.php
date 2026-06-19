<?php

namespace App\Dto\Coupon;

final class CouponOutput
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $type,
        public string $scope,
        public int $value,
        public ?int $maxDiscountCents,
        public int $minCartAmountCents,
        public ?int $maxCartAmountCents,
        public int $usageLimit,
        public int $usedCount,
        public ?int $perUserLimit,
        public bool $combineWithPromotions,
        public bool $isActive,
        public ?string $startsAt,
        public ?string $expiresAt,
        public ?array $buyXGetYConfig,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }
}
