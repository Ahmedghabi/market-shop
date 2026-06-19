<?php

namespace App\Dto\Favorite;

final class ShopFavoriteOutput
{
    public string $id;
    public string $shopId;
    public string $shopName;
    public string $shopSlug;
    public ?string $coverImage;
    public \DateTimeImmutable $createdAt;
}
