<?php

namespace App\ApiResource\Webhook;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\State\Webhook\WebhookProvider;
use App\State\Webhook\WebhookProcessor;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/admin/webhooks/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            provider: WebhookProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/admin/webhooks',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            provider: WebhookProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/webhooks',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: WebhookProcessor::class,
        ),
        new Put(
            uriTemplate: '/admin/webhooks/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: WebhookProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/webhooks/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: WebhookProcessor::class,
        ),
    ],
)]
final class WebhookResource
{
}
