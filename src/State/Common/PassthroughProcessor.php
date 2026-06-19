<?php

namespace App\State\Common;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

/** @implements ProcessorInterface<object, object|null> */
final class PassthroughProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        unset($operation, $uriVariables, $context);

        return is_object($data) ? $data : null;
    }
}
