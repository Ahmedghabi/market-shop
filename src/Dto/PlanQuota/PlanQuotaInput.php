<?php

namespace App\Dto\PlanQuota;

use Symfony\Component\Validator\Constraints as Assert;

final class PlanQuotaInput
{
    #[Assert\NotBlank]
    public string $planId;

    #[Assert\NotBlank]
    public string $quotaId;

    /**
     * Null means unlimited for this plan.
     */
    public ?int $limitValue = null;
}
