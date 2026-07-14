<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionCategoryInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 160)]
    public ?string $name = null;
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    public ?string $slug = null;
    public ?string $description = null;
    public ?bool $isActive = null;
    public ?int $position = null;
}
