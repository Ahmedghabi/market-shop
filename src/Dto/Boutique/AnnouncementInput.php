<?php

namespace App\Dto\Boutique;

final class AnnouncementInput
{
    public string $content = '';
    public ?string $description = null;
    public string $displayType = 'banner';
    public ?string $type = null;
    public ?string $title = null;
    public ?string $subtitle = null;
    public ?string $backgroundColor = null;
    public ?string $textColor = null;
    public ?string $borderColor = null;
    public ?string $buttonColor = null;
    public ?string $icon = null;
    public ?string $imageId = null;
    public ?string $buttonText = null;
    public ?string $linkUrl = null;
    public ?string $buttonUrl = null;
    public int $priority = 0;
    public bool $isDismissible = true;
    public string $displayMode = 'FIXED';
    public string $position = 'TOP_PAGE';
    /** @var list<string> */
    public array $displayPages = ['all'];
    /** @var list<string> */
    public array $categoryIds = [];
    /** @var list<string> */
    public array $productIds = [];
    /** @var array<string, mixed> */
    public array $settings = [];
    public bool $active = true;
    public bool $isGlobal = false;
    public ?string $startsAt = null;
    public ?string $endsAt = null;
}
