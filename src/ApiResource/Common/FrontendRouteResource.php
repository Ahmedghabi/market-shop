<?php

namespace App\ApiResource\Common;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Common\FrontendRouteProvider;

#[ApiResource(
    shortName: 'FrontendRoute',
    operations: [
        new GetCollection(uriTemplate: '/frontend-routes', provider: FrontendRouteProvider::class),
        new Get(uriTemplate: '/frontend-routes/{slug}', provider: FrontendRouteProvider::class),
    ],
)]
final class FrontendRouteResource
{
    #[ApiProperty(identifier: true)]
    public string $slug;
    public string $title;
    public string $path;
    public string $section;
    public string $description;
    public ?string $stitchScreenId = null;
    public ?string $htmlPath = null;
    public ?string $imagePath = null;

    public function __construct(
        string $slug,
        string $title,
        string $path,
        string $section,
        string $description,
        ?string $stitchScreenId = null,
        ?string $htmlPath = null,
        ?string $imagePath = null,
    ) {
        $this->slug = $slug;
        $this->title = $title;
        $this->path = $path;
        $this->section = $section;
        $this->description = $description;
        $this->stitchScreenId = $stitchScreenId;
        $this->htmlPath = $htmlPath;
        $this->imagePath = $imagePath;
    }
}
