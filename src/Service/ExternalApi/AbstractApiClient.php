<?php

namespace App\Service\ExternalApi;

abstract class AbstractApiClient
{
    public function __construct(protected readonly string $baseUrl = '')
    {
    }
}
