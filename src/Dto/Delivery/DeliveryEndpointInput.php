<?php

namespace App\Dto\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryEndpointInput
{
    public ?string $companyId = null;

    #[Assert\NotBlank]
    public string $type = 'create_shipment';

    #[Assert\NotBlank]
    public string $name = '';

    #[Assert\NotBlank]
    public string $url = '';

    #[Assert\NotBlank]
    public string $httpMethod = 'POST';

    /** @var array<string, string> */
    public array $headers = [];

    public string $responseType = 'json';

    public bool $isActive = true;
}
