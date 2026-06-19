<?php

namespace App\ApiResource\Sponsor;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Sponsor\BoutiqueSponsorProcessor;
use App\State\Sponsor\BoutiqueSponsorProvider;

#[ApiResource(
    shortName: 'BoutiqueSponsor',
    operations: [
        new GetCollection(uriTemplate: '/sponsors'),
        new Post(uriTemplate: '/sponsors', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
        new Delete(uriTemplate: '/sponsors/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: BoutiqueSponsorProvider::class,
    processor: BoutiqueSponsorProcessor::class,
)]
final class BoutiqueSponsorResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $sponsorId = null;
    public ?string $name = null;
    public ?string $scope = null;
    public ?string $targetUrl = null;
    public int $position = 0;
    public bool $active = true;
}
