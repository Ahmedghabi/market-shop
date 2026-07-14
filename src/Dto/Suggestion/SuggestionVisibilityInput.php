<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionVisibilityInput
{
    #[Assert\NotBlank]
    public ?string $visibility = null;
}
