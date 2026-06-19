<?php

namespace App\ApiResource\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'StockMovement',
    operations: [
        new GetCollection(uriTemplate: '/stock-movements', security: "is_granted('ROLE_CAISSIER')"),
        new Post(uriTemplate: '/stock-movements', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class StockMovementResource
{
    public ?string $id = null;
    public ?string $productId = null;
    public ?string $type = null;
    public int $quantity = 0;
    public ?string $reason = null;
    public ?string $reference = null;
    public ?string $createdAt = null;
}
