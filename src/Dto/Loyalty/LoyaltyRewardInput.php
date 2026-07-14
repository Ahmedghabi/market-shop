<?php

namespace App\Dto\Loyalty;

use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyRewardInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name = '';

    public ?string $description = null;

    #[Assert\NotBlank]
    public string $typeCode = 'fixed_discount';

    /** @var array<string, mixed> */
    public array $config = [];

    #[Assert\Choice(['points', 'orders_count'])]
    public string $costType = 'points';

    #[Assert\PositiveOrZero]
    public int $costValue = 0;

    #[Assert\PositiveOrZero]
    public ?int $minOrderAmountCents = null;

    #[Assert\PositiveOrZero]
    public ?int $maxDiscountCents = null;

    #[Assert\PositiveOrZero]
    public ?int $minOrdersRequired = null;

    #[Assert\PositiveOrZero]
    public ?int $validityDays = null;

    public ?bool $combinableWithPromotions = null;

    public ?bool $combinableWithCoupons = null;

    public ?bool $combinableWithOtherDiscounts = null;

    public ?bool $combinableWithFreeShipping = null;

    #[Assert\PositiveOrZero]
    public ?int $usageLimit = null;

    #[Assert\PositiveOrZero]
    public ?int $usageLimitPerCustomer = null;

    public int $priority = 0;

    public bool $isActive = true;
}
