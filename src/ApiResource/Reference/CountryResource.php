<?php

namespace App\ApiResource\Reference;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Reference\CountryProvider;

#[ApiResource(
    shortName: 'Country',
    operations: [
        new GetCollection(
            uriTemplate: '/reference/countries',
            security: "is_granted('PUBLIC_ACCESS')",
            provider: CountryProvider::class,
        ),
    ],
)]
final class CountryResource
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $code = null;
    public ?string $phoneCode = null;
}
