<?php

namespace App\ApiResource\Customer;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'Customer',
    operations: [
        new GetCollection(uriTemplate: '/customers', security: "is_granted('ROLE_CAISSIER')"),
        new Post(uriTemplate: '/customers', security: "is_granted('ROLE_CAISSIER')"),
        new Get(uriTemplate: '/customers/{id}', security: "is_granted('ROLE_CAISSIER')"),
        new Patch(uriTemplate: '/customers/{id}', security: "is_granted('ROLE_CAISSIER')"),
        new Delete(uriTemplate: '/customers/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class CustomerResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $email = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
}
