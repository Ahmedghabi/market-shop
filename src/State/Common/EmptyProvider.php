<?php

namespace App\State\Common;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/** @implements ProviderInterface<object|array<object>|null> */
final class EmptyProvider implements ProviderInterface
{
    /** @return array<object>|object|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|object|null
    {
        unset($uriVariables, $context);

        if ($operation instanceof GetCollection) {
            return [];
        }

        return null;
    }
}
