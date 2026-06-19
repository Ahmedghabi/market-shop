<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'LoyaltyAccount',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/loyalty/accounts', security: "is_granted('ROLE_CAISSIER')"),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/loyalty/accounts', security: "is_granted('ROLE_CAISSIER')"),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/loyalty/accounts/{id}', security: "is_granted('ROLE_CUSTOMER')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class LoyaltyAccountResource
{
    public ?string $id = null;
    public ?string $customerId = null;
    public int $pointsBalance = 0;
}
