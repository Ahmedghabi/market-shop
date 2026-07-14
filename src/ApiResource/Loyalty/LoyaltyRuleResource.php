<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Loyalty\LoyaltyRuleInput;
use App\Dto\Loyalty\LoyaltyRuleOutput;
use App\State\Loyalty\LoyaltyRuleProcessor;
use App\State\Loyalty\LoyaltyRuleProvider;

#[ApiResource(
    shortName: 'LoyaltyRule',
    operations: [
        new GetCollection(
            uriTemplate: '/boutique/loyalty/rules',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyRuleOutput::class,
            provider: LoyaltyRuleProvider::class,
        ),
        new Post(
            uriTemplate: '/boutique/loyalty/rules',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyRuleInput::class,
            output: LoyaltyRuleOutput::class,
            processor: LoyaltyRuleProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutique/loyalty/rules/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyRuleOutput::class,
            provider: LoyaltyRuleProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutique/loyalty/rules/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyRuleInput::class,
            output: LoyaltyRuleOutput::class,
            provider: LoyaltyRuleProvider::class,
            processor: LoyaltyRuleProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutique/loyalty/rules/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: LoyaltyRuleProvider::class,
            processor: LoyaltyRuleProcessor::class,
        ),
    ],
)]
final class LoyaltyRuleResource
{
}
