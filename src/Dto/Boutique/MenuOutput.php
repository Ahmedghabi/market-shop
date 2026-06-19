<?php

namespace App\Dto\Boutique;

final class MenuOutput
{
    public string $id;
    public string $boutiqueId;
    public string $name;
    public string $position;
    public bool $isActive;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
    /** @var list<MenuItemOutput> */
    public array $items = [];
}
