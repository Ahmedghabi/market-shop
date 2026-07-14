<?php

namespace App\Dto\Subscription;

final class SubscriptionStatsOutput
{
    public int $activeSubscriptions = 0;
    public int $expiredSubscriptions = 0;
    public int $pendingSubscriptionRequests = 0;
    public int $revenueSubscriptionsTnd = 0;
    public int $revenueExtensionsTnd = 0;

    /** @var array<string, int> extension request status => count */
    public array $extensionRequestsByStatus = [];

    /** @var list<array{code: string, name: string, count: int}> */
    public array $mostRequestedExtensions = [];

    public int $activeExtensionGrants = 0;
    public int $expiredExtensionGrants = 0;
}
