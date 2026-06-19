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

#[ApiResource(
    shortName: 'ProductFilter',
    operations: [
        new GetCollection(
            uriTemplate: '/filters',
            provider: ProductFilterProvider::class,
        ),
        new Post(
            uriTemplate: '/filters',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: ProductFilterProcessor::class,
        ),
        new Get(
            uriTemplate: '/filters/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            provider: ProductFilterProvider::class,
        ),
        new Patch(
            uriTemplate: '/filters/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: ProductFilterProcessor::class,
        ),
        new Delete(
            uriTemplate: '/filters/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
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
    public ?string $slug = null;
    public string $type = 'select';
    public int $position = 0;
    public bool $active = true;
    public array $values = [];
}
