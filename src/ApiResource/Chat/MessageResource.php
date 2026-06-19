<?php

namespace App\ApiResource\Chat;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Chat\MessageProvider;
use App\State\Chat\MessageProcessor;

#[ApiResource(
    shortName: 'Message',
    operations: [
        new GetCollection(
            uriTemplate: '/conversations/{conversationId}/messages',
            provider: MessageProvider::class,
        ),
        new Post(
            uriTemplate: '/conversations/{conversationId}/messages',
            processor: MessageProcessor::class,
        ),
        new Get(
            uriTemplate: '/conversations/{conversationId}/messages/{id}',
            provider: MessageProvider::class,
        ),
    ],
)]
final class MessageResource
{
    public ?string $id = null;
    public string $conversationId;
    public string $senderType = 'user';
    public string $content;
    public ?string $fileUrl = null;
    public ?string $fileType = null;
    public bool $read = false;
    public string $createdAt;
    public ?string $readAt = null;
}
