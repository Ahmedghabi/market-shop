<?php

namespace App\ApiResource\Chat;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use App\State\Chat\ConversationProvider;
use App\State\Chat\ConversationProcessor;

#[ApiResource(
    shortName: 'Conversation',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/conversations',
            provider: ConversationProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/conversations',
            processor: ConversationProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/conversations/{id}',
            provider: ConversationProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/conversations/{id}',
            processor: ConversationProcessor::class,
        ),
    ],
)]
final class ConversationResource
{
    public ?string $id = null;
    public string $boutiqueId;
    public ?string $userId = null;
    public ?string $guestName = null;
    public ?string $guestEmail = null;
    public ?string $guestPhone = null;
    public bool $active = true;
    public string $createdAt;
    public ?string $updatedAt = null;
    public int $unreadCount = 0;
    public ?array $lastMessage = null;
}
