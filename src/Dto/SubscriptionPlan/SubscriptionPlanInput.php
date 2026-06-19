<?php

namespace App\Dto\SubscriptionPlan;

use Symfony\Component\Validator\Constraints as Assert;

final class SubscriptionPlanInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name;

    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public int $durationMonths;

    public int $priceTnd = 0;

    public bool $isFree = false;

    public bool $isVisible = true;

    public bool $isActive = true;

    /** @var list<string>|null */
    public ?array $modules = null;
}
