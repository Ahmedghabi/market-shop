<?php

namespace App\Dto\Media;

final class MediaOutput
{
    public string $id;
    public string $boutiqueId;
    public string $type;
    public string $originalName;
    public string $fileName;
    public string $extension;
    public string $mimeType;
    public int $size;
    public ?int $width;
    public ?int $height;
    public ?float $duration;
    public string $url;
    public ?string $thumbnailUrl;
    public ?string $compressedUrl;
    public ?string $altText;
    public bool $isPublic;
    /** @var array<string, string> */
    public array $thumbnails = [];
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
