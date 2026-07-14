<?php

namespace App\ApiResource\Subscription;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Subscription\SubscriptionStatsOutput;
use App\State\Subscription\SubscriptionStatsProvider;

#[ApiResource(
    shortName: 'SubscriptionStats',
    operations: [
        new Get(
            uriTemplate: '/admin/subscription/stats',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SubscriptionStatsOutput::class,
            provider: SubscriptionStatsProvider::class,
        ),
    ],
)]
final class SubscriptionStatsResource
{
    public int $activeSubscriptions = 0;
    public int $expiredSubscriptions = 0;
    public int $pendingSubscriptionRequests = 0;
    public int $revenueSubscriptionsTnd = 0;
    public int $revenueExtensionsTnd = 0;
    public array $extensionRequestsByStatus = [];
    public array $mostRequestedExtensions = [];
    public int $activeExtensionGrants = 0;
    public int $expiredExtensionGrants = 0;
}
