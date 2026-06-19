<?php

namespace App\Dto\Cart;

final class CartItemOutput
{
    public string $id;
    public ?string $productId;
    public ?string $productName;
    public ?string $sku;
    public int $quantity;
    public int $unitPriceCents;
    public int $totalCents;
}
