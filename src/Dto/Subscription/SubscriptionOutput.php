<?php

namespace App\Dto\Subscription;

use App\Enum\PlanType;
use App\Enum\SubscriptionStatus;

final class SubscriptionOutput
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public PlanType $plan;
    public SubscriptionStatus $status = SubscriptionStatus::Pending;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $acceptedBy = null;
    public ?string $acceptedAt = null;
    public ?string $createdAt = null;
    public int $priceCents = 0;
}
