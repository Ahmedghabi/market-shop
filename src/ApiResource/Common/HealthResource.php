<?php

namespace App\ApiResource\Common;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\State\Common\HealthProvider;

#[ApiResource(
    shortName: 'Health',
    operations: [
        new Get(uriTemplate: '/health', provider: HealthProvider::class),
    ]
)]
final class HealthResource
{
    public string $status = 'ok';
}
