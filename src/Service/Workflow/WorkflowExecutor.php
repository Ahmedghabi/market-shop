<?php

namespace App\Service\Workflow;

use App\Service\Workflow\Transition\TransitionInterface;

final readonly class WorkflowExecutor
{
    /** @param iterable<TransitionInterface> $transitions */
    public function __construct(private iterable $transitions)
    {
    }

    /** @param array<string, mixed> $context */
    public function execute(array $context): array
    {
        foreach ($this->transitions as $transition) {
            if ($transition->supports($context)) {
                $context = $transition->apply($context);
            }
        }

        return $context;
    }
}
