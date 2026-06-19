<?php

namespace App\Dto\Catalog;

use Symfony\Component\Validator\Constraints as Assert;

final class CategoryInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name;

    #[Assert\Length(max: 180)]
    public ?string $slug = null;

    public ?string $parentId = null;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;

    public ?string $image = null;

    public ?string $banner = null;

    public bool $isActive = true;

    public bool $isFeatured = false;

    public bool $showInHeader = false;

    public bool $showOnHomepage = false;

    public ?string $homepageDisplayType = null;

    public int $homepagePosition = 0;

    public int $menuPosition = 0;

    public bool $showCategoryPage = true;

    public int $productsLimit = 0;

    public ?string $metaTitle = null;

    public ?string $metaDescription = null;

    public ?string $metaKeywords = null;

    public ?string $ogTitle = null;

    public ?string $ogDescription = null;

    public ?string $ogImage = null;
}
