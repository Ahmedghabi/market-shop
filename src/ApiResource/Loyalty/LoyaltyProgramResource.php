<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Dto\Loyalty\LoyaltyProgramInput;
use App\Dto\Loyalty\LoyaltyProgramOutput;
use App\State\Loyalty\LoyaltyProgramProcessor;
use App\State\Loyalty\LoyaltyProgramProvider;

#[ApiResource(
    shortName: 'LoyaltyProgram',
    operations: [
        new Get(
            uriTemplate: '/boutique/loyalty/program',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: LoyaltyProgramOutput::class,
            provider: LoyaltyProgramProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutique/loyalty/program',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: LoyaltyProgramInput::class,
            output: LoyaltyProgramOutput::class,
            processor: LoyaltyProgramProcessor::class,
        ),
    ],
)]
final class LoyaltyProgramResource
{
}
