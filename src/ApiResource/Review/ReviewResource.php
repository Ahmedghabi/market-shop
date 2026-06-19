<?php

namespace App\ApiResource\Review;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Review\ReviewInput;
use App\Dto\Review\ReviewOutput;
use App\State\Review\ReviewProcessor;
use App\State\Review\ReviewProvider;

#[ApiResource(
    shortName: 'Review',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/reviews',
            output: ReviewOutput::class,
            provider: ReviewProvider::class,
        ),
        new GetCollection(
            name: 'reviews_for_product',
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/reviews',
            output: ReviewOutput::class,
            provider: ReviewProvider::class,
        ),
        new GetCollection(
            name: 'platform_reviews',
            uriTemplate: '/platform/reviews',
            output: ReviewOutput::class,
            provider: ReviewProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/reviews',
            security: "is_granted('PUBLIC_ACCESS')",
            input: ReviewInput::class,
            output: ReviewOutput::class,
            processor: ReviewProcessor::class,
        ),
        new Post(
            name: 'create_product_review',
            uriTemplate: '/boutiques/{boutiqueId}/products/{productId}/reviews',
            security: "is_granted('PUBLIC_ACCESS')",
            input: ReviewInput::class,
            output: ReviewOutput::class,
            processor: ReviewProcessor::class,
        ),
        new Post(
            name: 'create_platform_review',
            uriTemplate: '/platform/reviews',
            security: "is_granted('PUBLIC_ACCESS')",
            input: ReviewInput::class,
            output: ReviewOutput::class,
            processor: ReviewProcessor::class,
        ),
        new Get(
            uriTemplate: '/reviews/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            output: ReviewOutput::class,
            provider: ReviewProvider::class,
        ),
        new Patch(
            name: 'approve_review',
            uriTemplate: '/reviews/{id}/approve',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            input: false,
            output: ReviewOutput::class,
            processor: ReviewProcessor::class,
        ),
        new Patch(
            name: 'reject_review',
            uriTemplate: '/reviews/{id}/reject',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')",
            input: false,
            output: ReviewOutput::class,
            processor: ReviewProcessor::class,
        ),
    ],
    paginationItemsPerPage: 30,
)]
final class ReviewResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $productId = null;
    public ?string $authorName = null;
    public int $rating = 5;
    public ?string $comment = null;
    public ?string $status = null;
    public ?\DateTimeImmutable $createdAt = null;
}
