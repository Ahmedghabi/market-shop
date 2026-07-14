<?php

namespace App\Dto\Subscription;

final class SubscriptionSummaryOutput
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

    /** @var list<array{code: string, name: string, unit: string|null, limit: int|null, usage: int, remaining: int|null}> */
    public array $quotas = [];

    /** @var list<string> */
    public array $accessibleModules = [];

    /** @var list<array{id: string, code: string, name: string}> */
    public array $accessibleThemes = [];

    /** @var list<array{id: string, extensionCode: string, extensionName: string, type: string, activatedAt: string, expiresAt: string|null}> */
    public array $activeExtensions = [];

    /** @var list<array{id: string, extensionCode: string, extensionName: string, status: string, priceTnd: int, requestedAt: string}> */
    public array $pendingRequests = [];

    public int $expiredExtensionsCount = 0;
}
