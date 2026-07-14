<?php

namespace App\Dto\Subscription;

final class PlanChangePreviewOutput
{
    public ?string $currentPlanId = null;
    public ?string $currentPlanName = null;
    public string $newPlanId;
    public string $newPlanName;
    public int $newPlanPriceTnd = 0;
    public string $currency = 'TND';
    public int $durationMonths = 1;

    /** @var list<array{code: string, name: string, currentLimit: int|null, newLimit: int|null, currentUsage: int, diff: int|null}> */
    public array $quotaChanges = [];

    /** @var list<string> */
    public array $modulesGained = [];

    /** @var list<string> */
    public array $modulesLost = [];

    /** @var list<array{code: string, name: string}> */
    public array $themesGained = [];

    /** @var list<array{code: string, name: string}> */
    public array $themesLost = [];

    /** @var list<array{code: string, name: string, compatible: bool}> */
    public array $extensionCompatibility = [];

    public bool $isRenewal = false;
    public ?string $projectedEndDate = null;
}
