<?php

namespace App\ApiResource\Subscription;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Subscription\PlanChangePreviewOutput;
use App\State\Subscription\PlanChangePreviewProvider;

#[ApiResource(
    shortName: 'PlanChangePreview',
    operations: [
        new Get(
            uriTemplate: '/subscription/plan-change/preview',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: PlanChangePreviewOutput::class,
            provider: PlanChangePreviewProvider::class,
        ),
    ],
)]
final class PlanChangePreviewResource
{
}
