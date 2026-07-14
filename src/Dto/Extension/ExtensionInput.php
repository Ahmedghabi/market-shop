<?php

namespace App\Dto\Extension;

use Symfony\Component\Validator\Constraints as Assert;

final class ExtensionInput
{
    #[Assert\NotBlank]
    public string $code;

    #[Assert\NotBlank]
    public string $name;

    public ?string $description = null;

    #[Assert\Choice(choices: ['quota_boost', 'module', 'theme', 'service'])]
    public string $type = 'service';

    public ?string $targetCode = null;
    public ?int $value = null;
    public int $priceTnd = 0;
    public ?int $durationMonths = null;
    public bool $requiresValidation = true;
    public bool $isActive = true;
    public ?string $icon = null;
}
