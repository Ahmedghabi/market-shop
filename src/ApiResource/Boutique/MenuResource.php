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
            uriTemplate: '/boutiques/{boutiqueId}/menus',
            uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            output: \App\Dto\Boutique\MenuOutput::class,
            provider: MenuProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/menus',
            uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuInput::class,
            processor: MenuProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            output: \App\Dto\Boutique\MenuOutput::class,
            provider: MenuProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuInput::class,
            processor: MenuProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: MenuProcessor::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{menuId}/items',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'menuId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuItemInput::class,
            processor: MenuProcessor::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{menuId}/items/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'menuId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Boutique\MenuItemInput::class,
            processor: MenuProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/menus/{menuId}/items/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
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
