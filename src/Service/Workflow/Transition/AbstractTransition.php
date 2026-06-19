<?php

namespace App\Service\Workflow\Transition;

abstract class AbstractTransition implements TransitionInterface
{
    public function supports(array $context): bool
    {
        return true;
    }
}
