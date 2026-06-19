<?php

namespace App\ApiResource\Chat;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\Chat\ChatbotConfigProcessor;
use App\State\Chat\ChatbotConfigProvider;

#[ApiResource(
    shortName: 'ChatbotConfig',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/chatbot-configs',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Post(
            uriTemplate: '/admin/chatbot-configs',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Get(
            uriTemplate: '/admin/chatbot-configs/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Patch(
            uriTemplate: '/admin/chatbot-configs/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/chatbot',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: ChatbotConfigProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/chatbot',
            uriVariables: [
                'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
            ],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: ChatbotConfigProvider::class,
            processor: ChatbotConfigProcessor::class,
        ),
    ],
)]
final class ChatbotConfigResource
{
    public ?string $id = null;
    public string $boutiqueId;
    public string $model = 'llama3.2:1b';
    public ?string $systemPrompt = null;
    public float $temperature = 0.7;
    public int $maxTokens = 512;
    public bool $isEnabled = false;
}
