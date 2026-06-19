<?php

namespace App\State\Example;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/** @implements ProviderInterface<object|null> */
final class ExampleProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        return null;
    }
}
