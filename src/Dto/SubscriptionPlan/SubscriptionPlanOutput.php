<?php

namespace App\Dto\SubscriptionPlan;

final class SubscriptionPlanOutput
{
    public ?string $id = null;
    public string $name;
    public ?string $description = null;
    public int $durationMonths;
    public int $priceTnd = 0;
    public bool $isFree = false;
    public bool $isVisible = true;
    public bool $isActive = true;
    /** @var list<string>|null */
    public ?array $modules = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
