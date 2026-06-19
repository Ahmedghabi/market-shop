<?php

namespace App\Dto\Boutique;

final class MenuItemOutput
{
    public string $id;
    public string $menuId;
    public string $title;
    public string $type;
    public ?string $target;
    public ?string $parentId;
    public int $position;
    public bool $isActive;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
