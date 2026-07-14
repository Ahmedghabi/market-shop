<?php

namespace App\ApiResource\Subscription;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Subscription\SubscriptionSummaryOutput;
use App\State\Subscription\SubscriptionSummaryProvider;

#[ApiResource(
    shortName: 'SubscriptionSummary',
    operations: [
        new Get(
            uriTemplate: '/subscription/summary',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: SubscriptionSummaryOutput::class,
            provider: SubscriptionSummaryProvider::class,
        ),
    ],
)]
final class SubscriptionSummaryResource
{
    public ?string $boutiqueId = null;
    public bool $isActive = false;
    public ?string $planId = null;
    public ?string $planName = null;
    public int $priceTnd = 0;
    public string $currency = 'TND';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?int $daysRemaining = null;
    public array $quotas = [];
    public array $accessibleModules = [];
    public array $accessibleThemes = [];
    public array $activeExtensions = [];
    public array $pendingRequests = [];
    public int $expiredExtensionsCount = 0;
}
