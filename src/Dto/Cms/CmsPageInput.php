<?php

namespace App\Dto\Cms;

final class CmsPageInput
{
    public ?string $boutiqueId = null;

    public string $title;
    public ?string $slug = null;
    public ?string $type = 'CUSTOM';
    public ?string $status = 'DRAFT';
    public ?string $description = null;
    public ?string $content = null;
    public ?string $template = null;
    public bool $isHomepage = false;
    public bool $showInHeader = false;
    public bool $showInFooter = false;
    public int $sortOrder = 0;
    public ?string $metaTitle = null;
    public ?string $metaDescription = null;
    public ?string $metaKeywords = null;
    public ?string $ogTitle = null;
    public ?string $ogDescription = null;
    public ?string $ogImage = null;
    public ?string $canonicalUrl = null;
    /** @var list<array{type: string, title?: ?string, content?: ?string, settings?: ?array, sortOrder?: int, isActive?: bool}> */
    public array $blocks = [];
}
