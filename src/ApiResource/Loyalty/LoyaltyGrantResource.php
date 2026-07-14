<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\Loyalty\LoyaltyGrantInput;
use App\Dto\Loyalty\LoyaltyGrantOutput;
use App\State\Loyalty\LoyaltyGrantProcessor;

#[ApiResource(
    shortName: 'LoyaltyGrant',
    operations: [
        new Post(
            uriTemplate: '/boutique/loyalty/grant',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyGrantInput::class,
            output: LoyaltyGrantOutput::class,
            processor: LoyaltyGrantProcessor::class,
        ),
    ],
)]
final class LoyaltyGrantResource
{
}
