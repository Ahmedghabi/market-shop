<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Dto\Loyalty\LoyaltyDashboardOutput;
use App\State\Loyalty\LoyaltyDashboardProvider;

#[ApiResource(
    shortName: 'LoyaltyDashboard',
    operations: [
        new Get(
            uriTemplate: '/boutique/loyalty/dashboard',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyDashboardOutput::class,
            provider: LoyaltyDashboardProvider::class,
        ),
    ],
)]
final class LoyaltyDashboardResource
{
}
