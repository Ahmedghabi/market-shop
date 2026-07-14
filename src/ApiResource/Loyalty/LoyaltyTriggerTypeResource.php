<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Loyalty\LoyaltyTypeOutput;
use App\State\Loyalty\LoyaltyTriggerTypeProvider;

#[ApiResource(
    shortName: 'LoyaltyTriggerType',
    operations: [
        new GetCollection(
            uriTemplate: '/boutique/loyalty/trigger-types',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyTypeOutput::class,
            provider: LoyaltyTriggerTypeProvider::class,
            paginationEnabled: false,
        ),
    ],
)]
final class LoyaltyTriggerTypeResource
{
}
