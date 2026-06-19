<?php

namespace App\Dto\Boutique;

final class MenuItemInput
{
    public string $title;
    public string $type = 'URL';
    public ?string $target = null;
    public ?string $parentId = null;
    public int $position = 0;
    public bool $isActive = true;
}
