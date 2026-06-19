<?php

namespace App\Dto\Catalog;

final class BrandOutput
{
    public string $id;
    public string $boutiqueId;
    public string $name;
    public string $slug;
    public ?string $logo;
    public ?string $description;
    public ?string $website;
    public bool $isActive;
    public int $productsCount = 0;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
