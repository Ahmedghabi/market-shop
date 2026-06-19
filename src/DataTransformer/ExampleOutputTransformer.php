<?php

namespace App\DataTransformer;

use App\Dto\Example\ExampleOutput;
use App\Entity\Example;

final class ExampleOutputTransformer
{
    public function transform(Example $example): ExampleOutput
    {
        return new ExampleOutput(
            id: $example->getId()->toRfc4122(),
            name: $example->getName(),
            status: $example->getStatus()->value,
        );
    }
}
