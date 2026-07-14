<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\Delivery\DeliveryVariableOutput;
use App\State\Delivery\DeliveryVariableProvider;

#[ApiResource(
    shortName: 'DeliveryVariable',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/delivery/variables',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: DeliveryVariableOutput::class,
            provider: DeliveryVariableProvider::class,
        ),
    ],
)]
final class DeliveryVariableResource
{
    public ?string $code = null;
}
