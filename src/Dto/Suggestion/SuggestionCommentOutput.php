<?php

namespace App\Dto\Suggestion;

final class SuggestionCommentOutput
{
    public ?string $id = null;
    public ?string $suggestionId = null;
    public ?string $userId = null;
    public ?string $authorName = null;
    public ?string $parentId = null;
    public string $content = '';
    public string $visibility = 'public';
    public string $createdAt = '';
    public ?string $updatedAt = null;
}
