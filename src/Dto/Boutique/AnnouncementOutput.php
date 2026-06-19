<?php

namespace App\Dto\Boutique;

final class AnnouncementOutput
{
    public string $id;
    public ?string $boutiqueId;
    public string $content;
    public ?string $description;
    public string $displayType;
    public string $type;
    public ?string $title;
    public ?string $subtitle;
    public ?string $backgroundColor;
    public ?string $textColor;
    public ?string $borderColor;
    public ?string $buttonColor;
    public ?string $icon;
    public ?string $imageId;
    public ?string $buttonText;
    public ?string $linkUrl;
    public ?string $buttonUrl;
    public int $priority;
    public bool $isDismissible;
    public string $displayMode;
    public string $position;
    /** @var list<string> */
    public array $displayPages;
    /** @var list<string> */
    public array $categoryIds;
    /** @var list<string> */
    public array $productIds;
    /** @var array<string, mixed> */
    public array $settings;
    public bool $active;
    public bool $isGlobal;
    public bool $visible;
    public int $viewsCount;
    public int $clicksCount;
    public int $conversionCount;
    public ?string $startsAt;
    public ?string $endsAt;
    public string $createdAt;
    public ?string $updatedAt;
}
