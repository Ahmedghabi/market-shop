<?php

namespace App\Dto\Suggestion;

final class SuggestionOutput
{
    public ?string $id = null;
    public string $title = '';
    public string $description = '';
    public ?string $categoryId = null;
    public ?string $categoryName = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public ?string $authorId = null;
    public ?string $authorName = null;
    public string $status = 'draft';
    public string $visibility = 'private';
    public bool $isPublished = false;
    public ?string $officialResponse = null;
    public ?string $officialResponseBy = null;
    public array $reactionCounts = [];
    public int $reactionCount = 0;
    public int $commentCount = 0;
    public ?string $currentUserReaction = null;
    public bool $showAuthorPublic = false;
    public bool $showBoutiquePublic = true;
    public string $createdAt = '';
    public ?string $updatedAt = null;
    public ?string $publishedAt = null;
    public ?string $closedAt = null;
    public array $history = [];
    public array $comments = [];
}
