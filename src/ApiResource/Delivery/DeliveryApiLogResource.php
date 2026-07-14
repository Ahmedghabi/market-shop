<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Delivery\DeliveryApiLogOutput;
use App\State\Delivery\DeliveryApiLogProvider;

#[ApiResource(
    shortName: 'DeliveryApiLog',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/delivery/api-logs',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: DeliveryApiLogOutput::class,
            provider: DeliveryApiLogProvider::class,
        ),
    ],
)]
final class DeliveryApiLogResource
{
    public ?string $id = null;
}
