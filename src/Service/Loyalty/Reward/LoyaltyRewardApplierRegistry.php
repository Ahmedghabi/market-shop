<?php

namespace App\Service\Loyalty\Reward;

use App\Contract\Loyalty\LoyaltyRewardApplierInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class LoyaltyRewardApplierRegistry
{
    /** @var array<string, LoyaltyRewardApplierInterface> */
    private array $appliers = [];

    /** @param iterable<LoyaltyRewardApplierInterface> $appliers */
    public function __construct(
        #[AutowireIterator('app.loyalty.reward_applier')]
        iterable $appliers,
    ) {
        foreach ($appliers as $applier) {
            $this->appliers[$applier->getCode()] = $applier;
        }
    }

    public function find(string $typeCode): ?LoyaltyRewardApplierInterface
    {
        return $this->appliers[$typeCode] ?? null;
    }

    public function has(string $typeCode): bool
    {
        return isset($this->appliers[$typeCode]);
    }

    /** @return list<array{code: string, label: string}> */
    public function describeAll(): array
    {
        return array_map(
            static fn (LoyaltyRewardApplierInterface $applier): array => [
                'code' => $applier->getCode(),
                'label' => $applier->getLabel(),
            ],
            array_values($this->appliers),
        );
    }
}
