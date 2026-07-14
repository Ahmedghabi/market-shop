<?php

namespace App\Dto\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

final class DeliveryCompanyInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public string $name = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    #[Assert\Regex('/^[a-z0-9-]+$/')]
    public string $slug = '';

    #[Assert\NotBlank]
    #[Assert\Url]
    public string $baseUrl = '';

    #[Assert\NotBlank]
    public string $provider = 'generic_http';

    #[Assert\NotBlank]
    public string $authType = 'basic';

    /** @var array<string, mixed> */
    public array $authConfig = [];

    /** @var array<string, mixed> */
    public array $mappingConfig = [];

    /** @var array<string, mixed> */
    public array $parametersConfig = [];

    public ?string $logoUrl = null;

    public ?string $description = null;

    public bool $isActive = true;
}
