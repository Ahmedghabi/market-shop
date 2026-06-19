<?php

namespace App\ApiResource\Boutique;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'BoutiqueUser',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/users', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/users', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/users/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
        new Patch(uriTemplate: '/boutiques/{boutiqueId}/users/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
        new Delete(uriTemplate: '/boutiques/{boutiqueId}/users/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class BoutiqueUserResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $email = null;
    public ?string $displayName = null;
    /** @var list<string> */
    public array $roles = [];
}
