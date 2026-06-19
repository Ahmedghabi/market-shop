<?php

namespace App\ApiResource\Catalog;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Catalog\BrandInput;
use App\Dto\Catalog\BrandOutput;
use App\State\Catalog\BrandProcessor;
use App\State\Catalog\BrandProvider;

#[ApiResource(
    shortName: 'Brand',
    operations: [
        new GetCollection(uriTemplate: '/brands', output: BrandOutput::class, provider: BrandProvider::class),
        new Post(uriTemplate: '/brands', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, input: BrandInput::class, output: BrandOutput::class, processor: BrandProcessor::class),
        new Get(uriTemplate: '/brands/{id}', uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], output: BrandOutput::class, provider: BrandProvider::class),
        new Patch(uriTemplate: '/brands/{id}', uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, input: BrandInput::class, output: BrandOutput::class, processor: BrandProcessor::class),
        new Delete(uriTemplate: '/brands/{id}', uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, processor: BrandProcessor::class),
    ],
)]
final class BrandResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $name = null;
    public ?string $slug = null;
}
