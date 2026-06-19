<?php

namespace App\Dto\Review;

final class ReviewOutput
{
    public string $id;
    public ?string $boutiqueId;
    public ?string $productId;
    public ?string $userId;
    public string $authorName;
    public ?string $authorEmail;
    public ?string $authorPhone;
    public int $rating;
    public ?string $title;
    public ?string $comment;
    /** @var list<string> */
    public array $images = [];
    public bool $isVerifiedPurchase;
    public string $status;
    public \DateTimeImmutable $createdAt;
}
