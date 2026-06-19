<?php

namespace App\ApiResource\Catalog;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Catalog\ProductInput;
use App\Dto\Catalog\ProductOutput;
use App\State\Catalog\ProductProcessor;
use App\State\Catalog\ProductProvider;

const BOUTIQUE_PRODUCT_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'Product',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/products', uriVariables: BOUTIQUE_PRODUCT_URI_VARIABLES, output: ProductOutput::class, provider: ProductProvider::class),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/products', uriVariables: BOUTIQUE_PRODUCT_URI_VARIABLES, security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, input: ProductInput::class, output: ProductOutput::class, processor: ProductProcessor::class),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/products/{id}', uriVariables: BOUTIQUE_PRODUCT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], output: ProductOutput::class, provider: ProductProvider::class),
        new Patch(uriTemplate: '/boutiques/{boutiqueId}/products/{id}', uriVariables: BOUTIQUE_PRODUCT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, input: ProductInput::class, output: ProductOutput::class, processor: ProductProcessor::class),
        new Delete(uriTemplate: '/boutiques/{boutiqueId}/products/{id}', uriVariables: BOUTIQUE_PRODUCT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, processor: ProductProcessor::class),
    ],
)]
final class ProductResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $categoryId = null;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $sku = null;
    public ?string $description = null;
    public int $priceCents = 0;
    public string $currency = 'EUR';
    public bool $active = true;
}
