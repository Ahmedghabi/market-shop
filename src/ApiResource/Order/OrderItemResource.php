<?php

namespace App\ApiResource\Order;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Common\EmptyProvider;

#[ApiResource(
    shortName: 'OrderItem',
    operations: [
        new GetCollection(uriTemplate: '/orders/{orderId}/items', security: "is_granted('ROLE_CUSTOMER')"),
    ],
    provider: EmptyProvider::class,
)]
final class OrderItemResource
{
    public ?string $id = null;
    public ?string $orderId = null;
    public ?string $productId = null;
    public ?string $productName = null;
    public ?string $sku = null;
    public int $quantity = 0;
    public int $unitPriceCents = 0;
    public int $discountCents = 0;
    public int $totalCents = 0;
}
