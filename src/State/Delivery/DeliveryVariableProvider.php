<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\DeliveryVariableOutput;
use App\Service\Delivery\DeliveryVariableRegistry;

final class DeliveryVariableProvider implements ProviderInterface
{
    public function __construct(
        private readonly DeliveryVariableRegistry $registry,
    ) {
    }

    /** @return list<DeliveryVariableOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(static function (array $entry): DeliveryVariableOutput {
            $output = new DeliveryVariableOutput();
            $output->code = $entry['code'];
            $output->label = $entry['label'];
            $output->category = $entry['category'];

            return $output;
        }, $this->registry->catalog());
    }
}
