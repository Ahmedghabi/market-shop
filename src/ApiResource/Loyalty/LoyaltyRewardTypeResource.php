<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Loyalty\LoyaltyTypeOutput;
use App\State\Loyalty\LoyaltyRewardTypeProvider;

#[ApiResource(
    shortName: 'LoyaltyRewardType',
    operations: [
        new GetCollection(
            uriTemplate: '/boutique/loyalty/reward-types',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyTypeOutput::class,
            provider: LoyaltyRewardTypeProvider::class,
            paginationEnabled: false,
        ),
    ],
)]
final class LoyaltyRewardTypeResource
{
}
