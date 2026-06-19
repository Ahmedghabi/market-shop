<?php

namespace App\State\Favorite;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Favorite\ProductFavoriteOutput;
use App\Dto\Favorite\ShopFavoriteOutput;
use App\Service\Favorite\FavoriteService;

/** @implements ProcessorInterface<ProductFavoriteOutput|ShopFavoriteOutput|null> */
final readonly class FavoriteProcessor implements ProcessorInterface
{
    public function __construct(private FavoriteService $favorites)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ProductFavoriteOutput|ShopFavoriteOutput|null
    {
        unset($data, $context);

        $operationName = $operation->getName() ?? '';

        return match (true) {
            'favorite_product_add' === $operationName => $this->favorites->addProductFavorite((string) ($uriVariables['productId'] ?? '')),
            'favorite_shop_add' === $operationName => $this->favorites->addShopFavorite((string) ($uriVariables['shopId'] ?? '')),
            $operation instanceof Delete && 'favorite_product_delete' === $operationName => $this->deleteProduct((string) ($uriVariables['productId'] ?? '')),
            $operation instanceof Delete && 'favorite_shop_delete' === $operationName => $this->deleteShop((string) ($uriVariables['shopId'] ?? '')),
            default => throw new \InvalidArgumentException('Unsupported favorite operation.'),
        };
    }

    private function deleteProduct(string $productId): null
    {
        $this->favorites->removeProductFavorite($productId);

        return null;
    }

    private function deleteShop(string $shopId): null
    {
        $this->favorites->removeShopFavorite($shopId);

        return null;
    }
}
