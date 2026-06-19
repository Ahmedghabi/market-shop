<?php

namespace App\Service\Workflow\Transition;

interface TransitionInterface
{
    /** @param array<string, mixed> $context */
    public function supports(array $context): bool;

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function apply(array $context): array;
}
