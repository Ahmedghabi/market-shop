<?php

namespace App\Dto\Loyalty;

use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyRuleInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name = '';

    public ?string $description = null;

    #[Assert\NotBlank]
    public string $triggerCode = 'order_amount';

    /** @var array<string, mixed> */
    public array $triggerConfig = [];

    #[Assert\PositiveOrZero]
    public int $rewardPoints = 0;

    public bool $isMultiplier = false;

    #[Assert\Positive]
    public float $multiplierValue = 1.0;

    /** @var list<string>|null */
    public ?array $appliesToTriggerCodes = null;

    public ?string $unlockedRewardId = null;

    public int $priority = 0;

    public bool $isActive = true;

    public bool $isCumulative = true;

    public ?string $startsAt = null;

    public ?string $endsAt = null;

    /** @var list<int>|null */
    public ?array $activeDaysOfWeek = null;

    #[Assert\PositiveOrZero]
    public ?int $maxTriggersPerCustomer = null;

    #[Assert\PositiveOrZero]
    public ?int $maxTriggersPerPeriod = null;

    #[Assert\Choice(choices: ['day', 'week', 'month'], message: 'Type de période invalide.')]
    public ?string $periodType = null;
}
