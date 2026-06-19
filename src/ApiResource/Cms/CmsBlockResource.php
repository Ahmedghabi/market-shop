<?php

namespace App\ApiResource\Cms;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Cms\CmsBlockProvider;
use App\State\Cms\CmsBlockProcessor;

#[ApiResource(
    shortName: 'CmsBlock',
    operations: [
        new GetCollection(
            uriTemplate: '/cms/blocks',
            output: \App\Dto\Cms\CmsBlockOutput::class,
            provider: CmsBlockProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{pageId}/blocks',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'pageId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Cms\CmsBlockInput::class,
            processor: CmsBlockProcessor::class,
        ),
        new Get(
            uriTemplate: '/cms/blocks/{id}',
            output: \App\Dto\Cms\CmsBlockOutput::class,
            provider: CmsBlockProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{pageId}/blocks/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'pageId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Cms\CmsBlockInput::class,
            processor: CmsBlockProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{pageId}/blocks/{id}',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'pageId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: CmsBlockProcessor::class,
        ),
    ],
)]
final class CmsBlockResource
{
    public ?string $id = null;
    public ?string $pageId = null;
    public ?string $type = null;
}
