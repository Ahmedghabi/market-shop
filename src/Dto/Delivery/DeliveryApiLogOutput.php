<?php

namespace App\Dto\Delivery;

final class DeliveryApiLogOutput
{
    public string $id;
    public string $deliveryCompanyId;
    public string $deliveryCompanyName;
    public ?string $boutiqueId;
    public ?string $endpointType;
    public string $requestMethod;
    public string $requestUrl;
    public ?array $requestBody;
    public ?int $responseStatus;
    public ?array $responseBody;
    public bool $success;
    public ?string $errorMessage;
    public ?int $durationMs;
    public string $createdAt;
}
