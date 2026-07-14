<?php

namespace App\Dto\Loyalty;

final class LoyaltyRuleOutput
{
    public string $id;
    public string $programId;
    public string $name;
    public ?string $description;
    public string $triggerCode;
    /** @var array<string, mixed> */
    public array $triggerConfig;
    public int $rewardPoints;
    public bool $isMultiplier;
    public float $multiplierValue;
    /** @var list<string>|null */
    public ?array $appliesToTriggerCodes;
    public ?string $unlockedRewardId;
    public int $priority;
    public bool $isActive;
    public bool $isCumulative;
    public ?string $startsAt;
    public ?string $endsAt;
    /** @var list<int>|null */
    public ?array $activeDaysOfWeek;
    public ?int $maxTriggersPerCustomer;
    public ?int $maxTriggersPerPeriod;
    public ?string $periodType;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
