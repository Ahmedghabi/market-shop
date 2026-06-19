<?php

namespace App\Dto\Favorite;

final class ProductFavoriteOutput
{
    public string $id;
    public string $shopId;
    public string $shopName;
    public string $productId;
    public string $productName;
    public string $productSlug;
    public ?string $sku;
    public ?string $image;
    public \DateTimeImmutable $createdAt;
}
