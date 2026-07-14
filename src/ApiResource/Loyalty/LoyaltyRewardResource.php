<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Loyalty\LoyaltyRewardInput;
use App\Dto\Loyalty\LoyaltyRewardOutput;
use App\State\Loyalty\LoyaltyRewardProcessor;
use App\State\Loyalty\LoyaltyRewardProvider;

#[ApiResource(
    shortName: 'LoyaltyReward',
    operations: [
        new GetCollection(
            uriTemplate: '/boutique/loyalty/rewards',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyRewardOutput::class,
            provider: LoyaltyRewardProvider::class,
        ),
        new Post(
            uriTemplate: '/boutique/loyalty/rewards',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyRewardInput::class,
            output: LoyaltyRewardOutput::class,
            processor: LoyaltyRewardProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutique/loyalty/rewards/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyRewardOutput::class,
            provider: LoyaltyRewardProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutique/loyalty/rewards/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyRewardInput::class,
            output: LoyaltyRewardOutput::class,
            provider: LoyaltyRewardProvider::class,
            processor: LoyaltyRewardProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutique/loyalty/rewards/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: LoyaltyRewardProvider::class,
            processor: LoyaltyRewardProcessor::class,
        ),
    ],
)]
final class LoyaltyRewardResource
{
}
