<?php

namespace App\Dto\Suggestion;

use Symfony\Component\Validator\Constraints as Assert;

final class SuggestionCommentInput
{
    #[Assert\NotBlank]
    public ?string $content = null;
    public ?string $visibility = null;
    public ?string $parentId = null;
}
