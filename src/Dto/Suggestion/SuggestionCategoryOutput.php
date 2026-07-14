<?php

namespace App\Dto\Suggestion;

final class SuggestionCategoryOutput
{
    public ?string $id = null;
    public string $name = '';
    public string $slug = '';
    public ?string $description = null;
    public bool $isActive = true;
    public int $position = 0;
    public string $createdAt = '';
    public ?string $updatedAt = null;
}
