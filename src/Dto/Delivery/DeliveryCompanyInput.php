<?php

namespace App\Dto\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryCompanyInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[Assert\Regex('/^[a-z0-9-]+$/')]
    public string $slug;

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $baseUrl;

    public ?string $authEndpoint = null;

    #[Assert\NotBlank]
    public string $submitOrderEndpoint = '/orders';

    public ?string $trackEndpoint = null;

    public ?string $description = null;

    public bool $isActive = true;
}
