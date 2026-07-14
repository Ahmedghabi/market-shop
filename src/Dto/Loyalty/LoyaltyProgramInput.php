<?php

namespace App\Dto\Loyalty;

use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyProgramInput
{
    public ?string $boutiqueId = null;

    public ?bool $isActive = null;

    #[Assert\Choice(['never', 'days_30', 'days_90', 'days_180', 'days_365', 'custom'])]
    public ?string $pointsValidityPolicy = null;

    #[Assert\PositiveOrZero]
    public ?int $customValidityDays = null;

    #[Assert\Positive]
    public ?int $pointValueCents = null;

    public ?bool $allowChooseAmount = null;

    public ?bool $allowUseAllPoints = null;

    public ?bool $allowRewardSelection = null;

    #[Assert\PositiveOrZero]
    public ?int $minPointsToRedeem = null;

    #[Assert\PositiveOrZero]
    public ?int $maxPointsPerOrder = null;

    #[Assert\PositiveOrZero]
    public ?int $maxDiscountCentsPerOrder = null;

    #[Assert\PositiveOrZero]
    public ?int $minOrderAmountCentsToRedeem = null;

    #[Assert\PositiveOrZero]
    public ?int $minOrdersCountToRedeem = null;

    public ?bool $combinableWithPromotions = null;

    public ?bool $combinableWithCoupons = null;

    public ?bool $combinableWithOtherDiscounts = null;

    public ?bool $combinableWithFreeShipping = null;

    public ?bool $returnUsedPointsOnCancel = null;

    public ?bool $revokeEarnedPointsOnCancel = null;

    /** @var list<string>|null */
    public ?array $calculationOrder = null;

    #[Assert\PositiveOrZero]
    public ?int $cacheTtlSeconds = null;
}
