<?php

namespace App\ApiResource\CustomerLoyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\CustomerLoyalty\CustomerLoyaltyOutput;
use App\State\CustomerLoyalty\CustomerLoyaltyProvider;

#[ApiResource(
    shortName: 'CustomerLoyalty',
    operations: [
        new GetCollection(
            uriTemplate: '/customer/loyalties',
            security: "is_granted('ROLE_CUSTOMER')",
            output: CustomerLoyaltyOutput::class,
            provider: CustomerLoyaltyProvider::class,
        ),
        new Get(
            uriTemplate: '/customer/loyalties/{id}',
            security: "is_granted('ROLE_CUSTOMER')",
            output: CustomerLoyaltyOutput::class,
            provider: CustomerLoyaltyProvider::class,
        ),
    ],
)]
final class CustomerLoyaltyResource
{
    public ?string $id = null;
    public ?string $customerId = null;
    public ?string $boutiqueId = null;
    public int $pointsBalance = 0;
    public int $totalEarned = 0;
    public int $totalUsed = 0;
}
