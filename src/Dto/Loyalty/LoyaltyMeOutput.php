<?php

namespace App\Dto\Loyalty;

final class LoyaltyMeOutput
{
    public bool $programActive;
    public string $boutiqueId;
    public int $pointsBalance = 0;
    public int $totalEarned = 0;
    public int $totalUsed = 0;
    public int $pointValueCents = 1;
    public bool $allowChooseAmount = true;
    public bool $allowUseAllPoints = true;
    public bool $allowRewardSelection = true;
    public int $minPointsToRedeem = 0;
    /** @var list<array{name: string, description: ?string, triggerCode: string, rewardPoints: int}> */
    public array $rules = [];
    /** @var list<array{points: int, expiresAt: string}> */
    public array $expiringSoon = [];
}
