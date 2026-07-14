<?php

namespace App\Dto\Delivery;

final class DeliveryEndpointOutput
{
    public string $id;
    public string $companyId;
    public string $type;
    public string $name;
    public string $url;
    public string $httpMethod;
    /** @var array<string, string> */
    public array $headers = [];
    public string $responseType;
    public bool $isActive;
    public string $createdAt;
    public ?string $updatedAt = null;
}
