<?php

namespace App\Dto\Loyalty;

final class LoyaltyDashboardOutput
{
    public int $members;
    public int $pointsDistributed;
    public int $pointsUsed;
    public int $pointsExpired;
    public int $rewardsRedeemed;
    public int $programCostCents;
    /** @var list<array{customerId: string, totalEarned: int, pointsBalance: int}> */
    public array $topCustomers = [];
}
