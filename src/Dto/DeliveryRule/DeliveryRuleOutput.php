<?php

namespace App\Dto\DeliveryRule;

final class DeliveryRuleOutput
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public int $priceCents,
        public ?float $minWeightKg,
        public ?float $maxWeightKg,
        public ?float $minDistanceKm,
        public ?float $maxDistanceKm,
        public ?int $minCartAmountCents,
        public ?int $maxCartAmountCents,
        public int $priority,
        public bool $isActive,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }
}
