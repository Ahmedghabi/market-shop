<?php

namespace App\ApiResource\Catalog;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Catalog\ProductImageProvider;
use App\State\Catalog\ProductImageProcessor;

#[ApiResource(
    shortName: 'ProductImage',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/images',
            provider: ProductImageProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/images',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: ProductImageProcessor::class,
            inputFormats: ['multipart' => ['multipart/form-data']],
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/images/{id}',
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/images/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
        ),
    ],
)]
final class ProductImageResource
{
    public ?string $id = null;
    public ?string $productId = null;
    public ?string $url = null;
    public ?string $smallUrl = null;
    public ?string $largeUrl = null;
    public int $position = 0;
    public ?string $alt = null;
}
