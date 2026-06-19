<?php

namespace App\Dto\Cms;

final class CmsBlockOutput
{
    public string $id;
    public string $pageId;
    public string $type;
    public ?string $title;
    public ?string $content;
    /** @var array<string, mixed>|null */
    public ?array $settings;
    public int $sortOrder;
    public bool $isActive;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
