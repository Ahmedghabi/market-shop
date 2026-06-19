<?php

namespace App\Dto\Example;

final readonly class ExampleOutput
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
    ) {
    }
}
