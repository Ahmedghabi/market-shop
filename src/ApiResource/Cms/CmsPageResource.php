<?php

namespace App\ApiResource\Cms;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Cms\CmsPageProvider;
use App\State\Cms\CmsPageProcessor;

const BOUTIQUE_CMS_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'CmsPage',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES,
            output: \App\Dto\Cms\CmsPageOutput::class,
            provider: CmsPageProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Cms\CmsPageInput::class,
            processor: CmsPageProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{id}',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            output: \App\Dto\Cms\CmsPageOutput::class,
            provider: CmsPageProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{id}',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: \App\Dto\Cms\CmsPageInput::class,
            processor: CmsPageProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{id}',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            processor: CmsPageProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{id}/blocks/{blockId}',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'), 'blockId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            output: \App\Dto\Cms\CmsBlockOutput::class,
            provider: CmsPageProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/cms/pages/{id}/blocks',
            uriVariables: BOUTIQUE_CMS_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            output: \App\Dto\Cms\CmsBlockOutput::class,
            provider: CmsPageProvider::class,
        ),
    ],
)]
final class CmsPageResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $title = null;
    public ?string $slug = null;
}
