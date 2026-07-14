<?php

namespace App\Dto\Suggestion;

final class SuggestionHistoryOutput
{
    public ?string $id = null;
    public ?string $oldStatus = null;
    public string $newStatus = '';
    public ?string $changedBy = null;
    public ?string $comment = null;
    public string $createdAt = '';
}
