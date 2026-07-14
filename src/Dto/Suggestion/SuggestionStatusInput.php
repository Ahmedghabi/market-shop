<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionStatusInput
{
    #[Assert\NotBlank]
    public ?string $status = null;
    public ?string $comment = null;
}
