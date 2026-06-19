<?php

namespace App\Dto\Boutique;

final class MenuInput
{
    public string $name;
    public string $position = 'HEADER';
    public bool $isActive = true;
}
