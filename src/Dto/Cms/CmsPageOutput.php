<?php

namespace App\Dto\Cms;

final class CmsPageOutput
{
    public string $id;
    public string $boutiqueId;
    public string $title;
    public string $slug;
    public string $type;
    public string $status;
    public ?string $description;
    public ?string $content;
    public ?string $template;
    public bool $isHomepage;
    public bool $showInHeader;
    public bool $showInFooter;
    public int $sortOrder;
    public ?\DateTimeImmutable $publishedAt;
    public ?string $metaTitle;
    public ?string $metaDescription;
    public ?string $metaKeywords;
    public ?string $ogTitle;
    public ?string $ogDescription;
    public ?string $ogImage;
    public ?string $canonicalUrl;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
    /** @var list<CmsBlockOutput> */
    public array $blocks = [];
}
