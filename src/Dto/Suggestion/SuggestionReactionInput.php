<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionReactionInput
{
    #[Assert\NotBlank]
    public ?string $type = null;
}
