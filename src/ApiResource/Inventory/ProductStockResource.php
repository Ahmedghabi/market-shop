<?php

namespace App\ApiResource\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'ProductStock',
    operations: [
        new Get(uriTemplate: '/products/{productId}/stock', security: "is_granted('ROLE_CAISSIER')"),
        new Patch(uriTemplate: '/products/{productId}/stock', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class ProductStockResource
{
    public ?string $productId = null;
    public int $quantity = 0;
    public int $reservedQuantity = 0;
    public int $availableQuantity = 0;
    public int $lowStockThreshold = 0;
}
