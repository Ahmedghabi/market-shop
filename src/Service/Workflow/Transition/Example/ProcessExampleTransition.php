<?php

namespace App\Service\Workflow\Transition\Example;

use App\Service\Workflow\Transition\AbstractTransition;

final class ProcessExampleTransition extends AbstractTransition
{
    public function apply(array $context): array
    {
        $context['processed'] = true;

        return $context;
    }
}
