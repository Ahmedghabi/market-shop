<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Catalog\ProductImageResource;
use App\Entity\ProductImage;
use App\Repository\ProductImageRepository;

/** @implements ProviderInterface<ProductImageResource> */
final readonly class ProductImageProvider implements ProviderInterface
{
    public function __construct(
        private ProductImageRepository $images,
    ) {
    }

    /** @return list<ProductImageResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        unset($operation, $context);

        $productId = $uriVariables['productId'] ?? '';

        return array_map(
            fn (ProductImage $image): ProductImageResource => $this->toResource($image),
            $this->images->findBy(['product' => $productId], ['position' => 'ASC']),
        );
    }

    public function toResource(ProductImage $image): ProductImageResource
    {
        $r = new ProductImageResource();
        $r->id = (string) $image->getId();
        $r->productId = (string) $image->getProduct()->getId();
        $r->url = $image->getUrl();
        $r->smallUrl = $image->getSmallUrl();
        $r->largeUrl = $image->getLargeUrl();
        $r->position = $image->getPosition();
        $r->alt = $image->getAlt();

        return $r;
    }
}
