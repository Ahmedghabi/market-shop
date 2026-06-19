<?php

namespace App\State\Common;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Common\HealthResource;

/** @implements ProviderInterface<HealthResource> */
final class HealthProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HealthResource
    {
        unset($operation, $uriVariables, $context);

        return new HealthResource();
    }
}
