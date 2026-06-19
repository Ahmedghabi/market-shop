<?php

namespace App\Dto\Boutique;

use Symfony\Component\Validator\Constraints as Assert;

final class BoutiqueInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    #[Assert\Regex('/^[a-z0-9-]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.')]
    #[Assert\Regex('/^[a-z].*/', message: 'Le slug doit commencer par une lettre.')]
    #[Assert\Regex('/[a-z0-9]$/', message: 'Le slug doit se terminer par une lettre ou un chiffre.')]
    #[Assert\Regex('/^(?!.*--)/', message: 'Le slug ne peut pas contenir de tirets doubles.')]
    public string $slug;

    #[Assert\Choice(['pending', 'active', 'rejected', 'suspended', 'archived'])]
    public string $status = 'pending';

    public ?string $description = null;

    public ?string $coverImage = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $website = null;

    public ?string $customDomain = null;

    public bool $isVerified = false;

    public bool $isFeatured = false;

    public ?string $rejectionReason = null;

    #[Assert\NotBlank]
    public string $primaryColor = '#3525cd';

    #[Assert\NotBlank]
    public string $secondaryColor = '#505f76';

    public ?string $domain = null;

    public ?string $logoUrl = null;

    public ?string $contactEmail = null;

    public ?string $contactPhone = null;

    public ?string $address = null;

    /** @var array<string, string> */
    public array $socialLinks = [];

    public ?string $metaPixelId = null;
}
