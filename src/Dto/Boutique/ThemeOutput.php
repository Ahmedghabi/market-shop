<?php

namespace App\Dto\Boutique;

final class ThemeOutput
{
    public string $id;
    public string $name;
    public string $code;
    public ?string $previewImage;
    public bool $isActive;
    public bool $isDefault;
    /** @var array<string, string> */
    public array $colorPalette = [];
    public ?string $description = null;
    public ?string $layout = null;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
