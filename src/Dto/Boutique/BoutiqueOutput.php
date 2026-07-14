<?php

namespace App\Dto\Boutique;

final class BoutiqueOutput
{
    public string $id;
    public string $name;
    public string $slug;
    public string $status;
    public ?string $ownerId = null;
    public ?string $description = null;
    public ?string $coverImage = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $website = null;
    public ?string $customDomain = null;
    public bool $isVerified = false;
    public bool $isFeatured = false;
    public ?string $approvedAt = null;
    public ?string $approvedBy = null;
    public ?string $rejectionReason = null;
    public string $primaryColor;
    public string $secondaryColor;
    public ?string $domain;
    public ?string $logoUrl;
    public ?string $contactEmail;
    public ?string $contactPhone;
    public ?string $address;
    public array $socialLinks;
    public ?string $metaPixelId = null;
    public string $subdomainUrl;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
    public int $usersCount = 0;
    public int $productsCount = 0;
    public int $ordersCount = 0;
    public float $totalRevenue = 0.0;
    public bool $hasActiveSubscription = false;
    public bool $isVisiblePublicly = false;

    public array $colorPalette = [];
    public ?string $theme = null;
    public ?string $fontFamily = null;
    public ?string $fontSize = null;
    public ?string $borderRadius = null;
    public array $iconSet = [];
    public array $headerConfig = [];
    public array $footerConfig = [];
    public array $navigationItems = [];
    public array $frontOfficePages = [];
    public array $featuredCategories = [];
}
