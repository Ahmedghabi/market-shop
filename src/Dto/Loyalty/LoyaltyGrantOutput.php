<?php

namespace App\Dto\Loyalty;

final class LoyaltyGrantOutput
{
    public string $customerId;
    public string $boutiqueId;
    public int $pointsBalance;
    public int $totalEarned;
    public int $totalUsed;
}
