<?php

namespace App\ApiResource\Media;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\State\Media\MediaProvider;
use App\State\Media\MediaProcessor;

const BOUTIQUE_MEDIA_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'Media',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/media', uriVariables: BOUTIQUE_MEDIA_URI_VARIABLES, output: \App\Dto\Media\MediaOutput::class, provider: MediaProvider::class),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/media/upload', uriVariables: BOUTIQUE_MEDIA_URI_VARIABLES, security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, inputFormats: ['multipart' => ['multipart/form-data']], processor: MediaProcessor::class),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/media/{id}', uriVariables: BOUTIQUE_MEDIA_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], output: \App\Dto\Media\MediaOutput::class, provider: MediaProvider::class),
        new Delete(uriTemplate: '/boutiques/{boutiqueId}/media/{id}', uriVariables: BOUTIQUE_MEDIA_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", read: false, processor: MediaProcessor::class),
    ],
)]
final class MediaResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $type = null;
    public ?string $fileName = null;
    public ?string $url = null;
}
