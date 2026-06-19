<?php

namespace App\Dto\Catalog;

use Symfony\Component\Validator\Constraints as Assert;

final class BrandInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public string $name;

    #[Assert\Length(max: 180)]
    public ?string $slug = null;

    public ?string $logo = null;

    public ?string $description = null;

    public ?string $website = null;

    public bool $isActive = true;
}
