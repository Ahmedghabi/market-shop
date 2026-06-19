<?php

namespace App\Dto\ShopModule;

use Symfony\Component\Validator\Constraints as Assert;

final class ShopModuleInput
{
    #[Assert\NotBlank]
    public string $moduleId;

    public bool $isEnabled = true;
}
