<?php

namespace App\Service\Loyalty\Trigger;

use App\Contract\Loyalty\LoyaltyTriggerEvaluatorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class LoyaltyTriggerRegistry
{
    /** @var array<string, LoyaltyTriggerEvaluatorInterface> */
    private array $evaluators = [];

    /** @param iterable<LoyaltyTriggerEvaluatorInterface> $evaluators */
    public function __construct(
        #[AutowireIterator('app.loyalty.trigger_evaluator')]
        iterable $evaluators,
    ) {
        foreach ($evaluators as $evaluator) {
            $this->evaluators[$evaluator->getCode()] = $evaluator;
        }
    }

    public function find(string $triggerCode): ?LoyaltyTriggerEvaluatorInterface
    {
        return $this->evaluators[$triggerCode] ?? null;
    }

    public function has(string $triggerCode): bool
    {
        return isset($this->evaluators[$triggerCode]);
    }

    /** @return list<array{code: string, label: string}> */
    public function describeAll(): array
    {
        return array_map(
            static fn (LoyaltyTriggerEvaluatorInterface $evaluator): array => [
                'code' => $evaluator->getCode(),
                'label' => $evaluator->getLabel(),
            ],
            array_values($this->evaluators),
        );
    }
}
