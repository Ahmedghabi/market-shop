<?php

namespace App\ApiResource\Boutique;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\State\Common\EmptyProvider;
use App\State\Common\PassthroughProcessor;

#[ApiResource(
    shortName: 'BoutiqueTheme',
    operations: [
        new Get(uriTemplate: '/theme'),
        new Patch(uriTemplate: '/theme', security: "is_granted('ROLE_BOUTIQUE_ADMIN')"),
    ],
    provider: EmptyProvider::class,
    processor: PassthroughProcessor::class,
)]
final class BoutiqueThemeResource
{
    public ?string $boutiqueId = null;
    public ?string $name = null;
    public ?string $logoUrl = null;
    public ?string $theme = null;

    /** @var array<string, string> */
    public array $colorPalette = [];

    /** @var array<string, string> */
    public array $iconSet = [];

    /** @var list<array{categoryId?: string, label: string, icon?: string, color?: string, position?: int}> */
    public array $featuredCategories = [];

    /** @var list<array{slug: string, label: string, enabled: bool, position?: int}> */
    public array $frontOfficePages = [];

    /** @var list<array{label: string, href: string, icon?: string, position?: int}> */
    public array $navigationItems = [];
}
