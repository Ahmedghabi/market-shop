<?php

namespace App\Dto\QuotaDefinition;

use Symfony\Component\Validator\Constraints as Assert;

final class QuotaDefinitionInput
{
    #[Assert\NotBlank]
    public string $code;

    #[Assert\NotBlank]
    public string $name;

    public ?string $description = null;
    public ?string $unit = null;
    public ?string $category = null;
    public ?string $icon = null;
    #[Assert\PositiveOrZero]
    public int $priceTnd = 0;
    public bool $isActive = true;
}
