<?php

namespace App\State\Favorite;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Favorite\ShopFavoriteOutput;
use App\Service\Favorite\FavoriteService;

/** @implements ProviderInterface<ShopFavoriteOutput> */
final readonly class ShopFavoriteProvider implements ProviderInterface
{
    public function __construct(private FavoriteService $favorites)
    {
    }

    /** @return list<ShopFavoriteOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $uriVariables, $context);

        return $this->favorites->listShopFavorites();
    }
}
