<?php

namespace App\ApiResource\Boutique;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Boutique\MenuProvider;
use App\State\Boutique\MenuProcessor;

#[ApiResource(
    shortName: 'Menu',
    operations: [
        new GetCollection(
            uriTemplate: '/menus',
            output: \App\Dto\Boutique\MenuOutput::class,
            provider: MenuProvider::class,
        ),
        new Post(
            uriTemplate: '/menus',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuInput::class,
            processor: MenuProcessor::class,
        ),
        new Get(
            uriTemplate: '/menus/{id}',
            uriVariables: [
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            output: \App\Dto\Boutique\MenuOutput::class,
            provider: MenuProvider::class,
        ),
        new Patch(
            uriTemplate: '/menus/{id}',
            uriVariables: [
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuInput::class,
            processor: MenuProcessor::class,
        ),
        new Delete(
            uriTemplate: '/menus/{id}',
            uriVariables: [
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: MenuProcessor::class,
        ),
        new Post(
            uriTemplate: '/menus/{menuId}/items',
            uriVariables: [
                'menuId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuItemInput::class,
            processor: MenuProcessor::class,
        ),
        new Patch(
            uriTemplate: '/menus/{menuId}/items/{id}',
            uriVariables: [
                'menuId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuItemInput::class,
            processor: MenuProcessor::class,
        ),
        new Delete(
            uriTemplate: '/menus/{menuId}/items/{id}',
            uriVariables: [
                'menuId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: MenuProcessor::class,
        ),
    ],
)]
final class MenuResource
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $position = null;
}
