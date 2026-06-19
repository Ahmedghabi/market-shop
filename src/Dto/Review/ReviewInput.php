<?php

namespace App\Dto\Review;

final class ReviewInput
{
    public ?string $boutiqueId = null;
    public ?string $productId = null;
    public ?string $authorName = null;
    public ?string $authorEmail = null;
    public ?string $authorPhone = null;
    public int $rating;
    public ?string $title = null;
    public ?string $comment = null;
    /** @var list<string> */
    public array $images = [];
}
