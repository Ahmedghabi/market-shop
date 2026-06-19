<?php

namespace App\ApiResource\Catalog;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Catalog\ProductFilterProvider;
use App\State\Catalog\ProductFilterProcessor;

const BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'ProductFilter',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/filters',
            uriVariables: BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES,
            provider: ProductFilterProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/filters',
            uriVariables: BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: ProductFilterProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/filters/{id}',
            uriVariables: BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            provider: ProductFilterProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/filters/{id}',
            uriVariables: BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: ProductFilterProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/filters/{id}',
            uriVariables: BOUTIQUE_PRODUCT_FILTER_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: ProductFilterProcessor::class,
        ),
    ],
)]
final class ProductFilterResource
{
    public ?string $id = null;
    public string $boutiqueId;
    public string $name;
    public string $slug;
    public string $type = 'select';
    public int $position = 0;
    public bool $active = true;
    public array $values = [];
}
