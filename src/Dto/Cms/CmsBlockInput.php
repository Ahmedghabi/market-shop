<?php

namespace App\Dto\Cms;

final class CmsBlockInput
{
    public string $type = 'TEXT';
    public ?string $title = null;
    public ?string $content = null;
    /** @var array<string, mixed>|null */
    public ?array $settings = null;
    public int $sortOrder = 0;
    public bool $isActive = true;
}
