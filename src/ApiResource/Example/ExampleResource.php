<?php

namespace App\ApiResource\Example;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\Example\ExampleInput;
use App\Dto\Example\ExampleOutput;
use App\State\Example\ExampleProcessor;

#[ApiResource(
    shortName: 'Example',
    operations: [
        new Post(
            uriTemplate: '/examples',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: ExampleInput::class,
            output: ExampleOutput::class,
            processor: ExampleProcessor::class,
        ),
    ],
)]
final class ExampleResource
{
}
