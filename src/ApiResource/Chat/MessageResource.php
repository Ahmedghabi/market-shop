<?php

namespace App\ApiResource\Chat;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\State\Chat\MessageProvider;
use App\State\Chat\MessageProcessor;

#[ApiResource(
    shortName: 'Message',
    operations: [
        new GetCollection(
            uriTemplate: '/conversations/{conversationId}/messages',
            uriVariables: ['conversationId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            provider: MessageProvider::class,
        ),
        new Post(
            uriTemplate: '/conversations/{conversationId}/messages',
            uriVariables: ['conversationId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            processor: MessageProcessor::class,
        ),
        new Get(
            uriTemplate: '/conversations/{conversationId}/messages/{id}',
            uriVariables: [
                'conversationId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
                'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('PUBLIC_ACCESS')",
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
