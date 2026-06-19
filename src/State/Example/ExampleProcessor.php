<?php

namespace App\State\Example;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Example\ExampleInput;
use App\Dto\Example\ExampleOutput;
use App\Service\ExampleService;

/** @implements ProcessorInterface<ExampleInput, ExampleOutput> */
final readonly class ExampleProcessor implements ProcessorInterface
{
    public function __construct(private ExampleService $exampleService)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ExampleOutput
    {
        if (!$data instanceof ExampleInput || null === $data->name) {
            throw new \InvalidArgumentException('Expected valid example input.');
        }

        return $this->exampleService->create($data->name);
    }
}
