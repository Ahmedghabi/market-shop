<?php

namespace App\ApiResource\ShopModule;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\ShopModule\ShopModuleOutput;
use App\State\ShopModule\ShopModuleProcessor;
use App\State\ShopModule\ShopModuleProvider;

const BOUTIQUE_SHOP_MODULE_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'ShopModule',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/modules',
            uriVariables: BOUTIQUE_SHOP_MODULE_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            output: ShopModuleOutput::class,
            provider: ShopModuleProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/modules',
            uriVariables: BOUTIQUE_SHOP_MODULE_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            output: ShopModuleOutput::class,
            processor: ShopModuleProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/modules/{id}',
            uriVariables: BOUTIQUE_SHOP_MODULE_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            output: ShopModuleOutput::class,
            provider: ShopModuleProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/modules/{id}',
            uriVariables: BOUTIQUE_SHOP_MODULE_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            output: ShopModuleOutput::class,
            processor: ShopModuleProcessor::class,
        ),
    ],
)]
final class ShopModuleResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $moduleId = null;
    public bool $isEnabled = true;
}
