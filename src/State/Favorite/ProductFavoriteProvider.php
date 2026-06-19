<?php

namespace App\State\Favorite;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Favorite\ProductFavoriteOutput;
use App\Service\Favorite\FavoriteService;

/** @implements ProviderInterface<ProductFavoriteOutput> */
final readonly class ProductFavoriteProvider implements ProviderInterface
{
    public function __construct(private FavoriteService $favorites)
    {
    }

    /** @return list<ProductFavoriteOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $uriVariables, $context);

        return $this->favorites->listProductFavorites();
    }
}
