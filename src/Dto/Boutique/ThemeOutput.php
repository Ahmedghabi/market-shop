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
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
