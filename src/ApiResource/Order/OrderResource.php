<?php

namespace App\ApiResource\Order;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'Order',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/orders', security: "is_granted('ROLE_CAISSIER')"),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/orders', security: "is_granted('ROLE_CUSTOMER')"),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/pos/orders', security: "is_granted('ROLE_CAISSIER')"),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/orders/{id}', security: "is_granted('ROLE_CUSTOMER')"),
        new Patch(uriTemplate: '/boutiques/{boutiqueId}/orders/{id}', security: "is_granted('ROLE_CAISSIER')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class OrderResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $customerId = null;
    public string $channel = 'online';
    public string $status = 'draft';
    public int $subtotalCents = 0;
    public int $discountCents = 0;
    public int $totalCents = 0;
    public string $currency = 'EUR';
    /** @var list<array{productId?: string, quantity: int}> */
    public array $items = [];
}
