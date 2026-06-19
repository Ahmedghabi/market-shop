<?php

namespace App\Service;

use App\Dto\Example\ExampleOutput;
use App\Entity\Example;

final class ExampleService
{
    public function create(string $name): ExampleOutput
    {
        $example = new Example($name);

        return new ExampleOutput(
            id: $example->getId()->toRfc4122(),
            name: $example->getName(),
            status: $example->getStatus()->value,
        );
    }
}
