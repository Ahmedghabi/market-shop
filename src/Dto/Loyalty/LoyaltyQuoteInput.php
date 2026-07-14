<?php

namespace App\Dto\Loyalty;

final class LoyaltyQuoteInput
{
    public bool $useAllPoints = false;

    public ?int $pointsToUse = null;

    public ?string $rewardId = null;

    public int $subtotalCents = 0;

    public int $alreadyAppliedDiscountsCents = 0;
}
