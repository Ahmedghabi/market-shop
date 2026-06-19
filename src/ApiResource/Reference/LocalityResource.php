<?php

namespace App\ApiResource\Reference;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Reference\LocalityProvider;

#[ApiResource(
    shortName: 'Locality',
    operations: [
        new GetCollection(
            uriTemplate: '/reference/localities',
            security: "is_granted('PUBLIC_ACCESS')",
            provider: LocalityProvider::class,
        ),
    ],
)]
final class LocalityResource
{
    public ?string $id = null;
    public ?string $countryId = null;
    public ?string $governorateId = null;
    public ?string $name = null;
    public ?string $postalCode = null;
}
