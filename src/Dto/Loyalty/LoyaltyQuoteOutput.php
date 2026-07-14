<?php

namespace App\Dto\Loyalty;

final class LoyaltyQuoteOutput
{
    public bool $success;
    public int $pointsUsed = 0;
    public int $discountCents = 0;
    public int $newSubtotalCents = 0;
    public bool $freeShipping = false;
    public ?string $freeProductId = null;
    public ?string $rewardId = null;
    public ?string $errorMessage = null;
}
