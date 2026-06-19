<?php

namespace App\ApiResource\Reference;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\Reference\GovernorateProvider;

#[ApiResource(
    shortName: 'Governorate',
    operations: [
        new GetCollection(
            uriTemplate: '/reference/governorates',
            security: "is_granted('PUBLIC_ACCESS')",
            provider: GovernorateProvider::class,
        ),
    ],
)]
final class GovernorateResource
{
    public ?string $id = null;
    public ?string $countryId = null;
    public ?string $name = null;
    public ?string $code = null;
}
