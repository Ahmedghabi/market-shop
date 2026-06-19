<?php

namespace App\Dto\DeliveryRule;

final class DeliveryRuleInput
{
    public ?string $name = null;
    public ?string $type = null;
    public ?int $priceCents = null;
    public ?float $minWeightKg = null;
    public ?float $maxWeightKg = null;
    public ?float $minDistanceKm = null;
    public ?float $maxDistanceKm = null;
    public ?int $minCartAmountCents = null;
    public ?int $maxCartAmountCents = null;
    public ?int $priority = null;
    public ?bool $isActive = null;
}
