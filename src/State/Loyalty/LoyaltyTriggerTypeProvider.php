<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyTypeOutput;
use App\Service\Loyalty\Trigger\LoyaltyTriggerRegistry;

/** @implements ProviderInterface<LoyaltyTypeOutput> */
final readonly class LoyaltyTriggerTypeProvider implements ProviderInterface
{
    public function __construct(
        private LoyaltyTriggerRegistry $registry,
    ) {
    }

    /** @return list<LoyaltyTypeOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return array_map(static function (array $descriptor): LoyaltyTypeOutput {
            $output = new LoyaltyTypeOutput();
            $output->code = $descriptor['code'];
            $output->label = $descriptor['label'];

            return $output;
        }, $this->registry->describeAll());
    }
}
