<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionOfficialResponseInput
{
    #[Assert\NotBlank]
    public ?string $response = null;
}
