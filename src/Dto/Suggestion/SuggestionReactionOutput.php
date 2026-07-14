<?php

namespace App\Dto\Suggestion;

final class SuggestionReactionOutput
{
    public ?string $id = null;
    public ?string $suggestionId = null;
    public ?string $userId = null;
    public string $type = '';
    public string $createdAt = '';
}
