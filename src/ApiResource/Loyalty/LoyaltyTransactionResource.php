<?php

namespace App\ApiResource\Loyalty;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'LoyaltyTransaction',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/loyalty/accounts/{accountId}/transactions', security: "is_granted('ROLE_CUSTOMER')"),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/loyalty/accounts/{accountId}/transactions', security: "is_granted('ROLE_CAISSIER')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class LoyaltyTransactionResource
{
    public ?string $id = null;
    public ?string $accountId = null;
    public ?string $type = null;
    public int $points = 0;
    public ?string $orderId = null;
    public ?string $reason = null;
    public ?string $createdAt = null;
}
