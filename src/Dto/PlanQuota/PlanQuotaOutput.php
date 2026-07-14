<?php

namespace App\Dto\PlanQuota;

final class PlanQuotaOutput
{
    public ?string $id = null;
    public ?string $planId = null;
    public ?string $planName = null;
    public ?string $quotaId = null;
    public ?string $quotaCode = null;
    public ?string $quotaName = null;
    public ?int $limitValue = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
