<?php

namespace App\Dto\CustomerLoyalty;

final class CustomerLoyaltyOutput
{
    public string $id;
    public string $customerId;
    public string $boutiqueId;
    public int $pointsBalance;
    public int $totalEarned;
    public int $totalUsed;
}
