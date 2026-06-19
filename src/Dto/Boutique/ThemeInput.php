<?php

namespace App\Dto\Boutique;

final class ThemeInput
{
    public string $name;
    public string $code;
    public ?string $previewImage = null;
    public bool $isActive = true;
    public bool $isDefault = false;
}
