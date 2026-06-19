<?php

namespace App\Dto\SubscriptionRequest;

final class SubscriptionRequestOutput
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public ?string $subscriptionPlanId = null;
    public ?string $subscriptionPlanName = null;
    public string $status = 'pending';
    public ?string $requestedAt = null;
    public ?string $approvedAt = null;
    public ?string $approvedBy = null;
}
