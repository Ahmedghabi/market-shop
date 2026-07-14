<?php

namespace App\ApiResource\PlanQuota;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\PlanQuota\PlanQuotaInput;
use App\Dto\PlanQuota\PlanQuotaOutput;
use App\State\PlanQuota\PlanQuotaProcessor;
use App\State\PlanQuota\PlanQuotaProvider;

#[ApiResource(
    shortName: 'PlanQuota',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/subscription-plans/{planId}/quotas',
            uriVariables: ['planId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: PlanQuotaOutput::class,
            provider: PlanQuotaProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/plan-quotas',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: PlanQuotaInput::class,
            output: PlanQuotaOutput::class,
            processor: PlanQuotaProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/plan-quotas/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: PlanQuotaOutput::class,
            provider: PlanQuotaProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/plan-quotas/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: PlanQuotaInput::class,
            output: PlanQuotaOutput::class,
            processor: PlanQuotaProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/plan-quotas/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: PlanQuotaProcessor::class,
        ),
    ],
)]
final class PlanQuotaResource
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
