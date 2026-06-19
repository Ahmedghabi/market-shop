<?php

namespace App\ApiResource\Sponsor;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Sponsor\SponsorProcessor;
use App\State\Sponsor\SponsorProvider;

#[ApiResource(
    shortName: 'Sponsor',
    operations: [
        new GetCollection(uriTemplate: '/sponsors'),
        new Post(uriTemplate: '/sponsors', security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Get(uriTemplate: '/sponsors/{id}'),
        new Patch(uriTemplate: '/sponsors/{id}', security: "is_granted('ROLE_SUPER_ADMIN')"),
        new Delete(uriTemplate: '/sponsors/{id}', security: "is_granted('ROLE_SUPER_ADMIN')"),
    ],
    provider: SponsorProvider::class,
    processor: SponsorProcessor::class,
)]
final class SponsorResource
{
    public ?string $id = null;
    public ?string $name = null;
    public string $scope = 'global';
    public ?string $logoUrl = null;
    public ?string $targetUrl = null;
    public bool $active = true;
}
