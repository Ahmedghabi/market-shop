<?php

namespace App\Dto\Loyalty;

final class LoyaltyProgramOutput
{
    public string $id;
    public string $boutiqueId;
    public bool $isActive;
    public string $pointsValidityPolicy;
    public ?int $customValidityDays;
    public ?int $validityDays;
    public int $pointValueCents;
    public bool $allowChooseAmount;
    public bool $allowUseAllPoints;
    public bool $allowRewardSelection;
    public int $minPointsToRedeem;
    public ?int $maxPointsPerOrder;
    public ?int $maxDiscountCentsPerOrder;
    public int $minOrderAmountCentsToRedeem;
    public int $minOrdersCountToRedeem;
    public bool $combinableWithPromotions;
    public bool $combinableWithCoupons;
    public bool $combinableWithOtherDiscounts;
    public bool $combinableWithFreeShipping;
    public bool $returnUsedPointsOnCancel;
    public bool $revokeEarnedPointsOnCancel;
    /** @var list<string> */
    public array $calculationOrder;
    public int $cacheTtlSeconds;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
