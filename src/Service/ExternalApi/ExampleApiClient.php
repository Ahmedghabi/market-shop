<?php

namespace App\Service\ExternalApi;

final class ExampleApiClient extends AbstractApiClient
{
    /** @return array<string, mixed> */
    public function fetchExample(string $id): array
    {
        return ['id' => $id];
    }
}
