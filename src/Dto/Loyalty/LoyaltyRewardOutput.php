<?php

namespace App\Dto\Loyalty;

final class LoyaltyRewardOutput
{
    public string $id;
    public string $programId;
    public string $name;
    public ?string $description;
    public string $typeCode;
    /** @var array<string, mixed> */
    public array $config;
    public string $costType;
    public int $costValue;
    public ?int $minOrderAmountCents;
    public ?int $maxDiscountCents;
    public ?int $minOrdersRequired;
    public ?int $validityDays;
    public ?bool $combinableWithPromotions;
    public ?bool $combinableWithCoupons;
    public ?bool $combinableWithOtherDiscounts;
    public ?bool $combinableWithFreeShipping;
    public ?int $usageLimit;
    public ?int $usageLimitPerCustomer;
    public int $priority;
    public bool $isActive;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
