<?php

namespace App\Dto\Delivery;

final class DeliveryCompanyOutput
{
    public string $id;
    public string $name;
    public string $slug;
    public string $baseUrl;
    public ?string $authEndpoint;
    public string $submitOrderEndpoint;
    public ?string $trackEndpoint;
    public ?string $description;
    public bool $isActive;
}
