<?php

namespace App\Service\Workflow;

final class WorkflowProvider
{
    /** @return array<string, mixed> */
    public function get(string $name): array
    {
        return ['name' => $name, 'transitions' => []];
    }
}
