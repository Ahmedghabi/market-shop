<?php

namespace App\Dto\Catalog;

final class CategoryOutput
{
    public string $id;
    public string $boutiqueId;
    public string $name;
    public string $slug;
    public ?string $parentId;
    public ?string $description;
    public ?string $image;
    public ?string $banner;
    public bool $isActive;
    public bool $isFeatured;
    public bool $showInHeader;
    public bool $showOnHomepage;
    public ?string $homepageDisplayType;
    public int $homepagePosition;
    public int $menuPosition;
    public bool $showCategoryPage;
    public int $productsLimit;
    public ?string $metaTitle;
    public ?string $metaDescription;
    public ?string $metaKeywords;
    public ?string $ogTitle;
    public ?string $ogDescription;
    public ?string $ogImage;
    public int $productsCount = 0;
    /** @var list<array{id: string, name: string, slug: string, productsCount: int}> */
    public array $children = [];
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
