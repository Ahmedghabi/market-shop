<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Dto\Loyalty\LoyaltyAvailableRewardOutput;
use App\Dto\Loyalty\LoyaltyMeOutput;
use App\Dto\Loyalty\LoyaltyQuoteInput;
use App\Dto\Loyalty\LoyaltyQuoteOutput;
use App\Dto\Loyalty\LoyaltyTransactionOutput;
use App\State\Loyalty\LoyaltyAvailableRewardProvider;
use App\State\Loyalty\LoyaltyHistoryProvider;
use App\State\Loyalty\LoyaltyMeProvider;
use App\State\Loyalty\LoyaltyQuoteProcessor;

#[ApiResource(
    shortName: 'LoyaltyMe',
    operations: [
        new Get(
            uriTemplate: '/me/loyalty',
            security: "is_granted('ROLE_CUSTOMER')",
            output: LoyaltyMeOutput::class,
            provider: LoyaltyMeProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/me/loyalty/history',
            security: "is_granted('ROLE_CUSTOMER')",
            output: LoyaltyTransactionOutput::class,
            provider: LoyaltyHistoryProvider::class,
            paginationEnabled: false,
        ),
        new GetCollection(
            uriTemplate: '/me/loyalty/rewards',
            security: "is_granted('ROLE_CUSTOMER')",
            output: LoyaltyAvailableRewardOutput::class,
            provider: LoyaltyAvailableRewardProvider::class,
            paginationEnabled: false,
        ),
        new Post(
            uriTemplate: '/me/loyalty/quote',
            security: "is_granted('ROLE_CUSTOMER')",
            input: LoyaltyQuoteInput::class,
            output: LoyaltyQuoteOutput::class,
            processor: LoyaltyQuoteProcessor::class,
        ),
    ],
)]
final class LoyaltyMeResource
{
}
