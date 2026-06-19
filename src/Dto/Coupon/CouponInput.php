<?php

namespace App\Dto\Coupon;

final class CouponInput
{
    public ?string $code = null;
    public ?string $name = null;
    public ?string $type = null;
    public ?string $scope = null;
    public ?int $value = null;
    public ?int $maxDiscountCents = null;
    public ?int $minCartAmountCents = null;
    public ?int $maxCartAmountCents = null;
    public ?int $usageLimit = null;
    public ?int $perUserLimit = null;
    public ?bool $combineWithPromotions = null;
    public ?bool $isActive = null;
    public ?string $startsAt = null;
    public ?string $expiresAt = null;
    public ?array $productIds = null;
    public ?array $categoryIds = null;
}
