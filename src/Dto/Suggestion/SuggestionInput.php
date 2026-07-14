<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionInput
{
    #[Assert\Length(max: 255)]
    public ?string $title = null;

    public ?string $description = null;

    public ?string $categoryId = null;
    public ?bool $showAuthorPublic = null;
    public ?bool $showBoutiquePublic = null;
}
