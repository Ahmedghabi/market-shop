<?php

namespace App\ApiResource\Notification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Notification\NotificationProviderInput;
use App\Dto\Notification\NotificationProviderOutput;
use App\State\Notification\NotificationProviderCatalogProcessor;
use App\State\Notification\NotificationProviderCatalogProvider;

#[ApiResource(
    shortName: 'NotificationProviderCatalog',
    operations: [
        new GetCollection(uriTemplate: '/admin/notification-providers', security: "is_granted('ROLE_SUPER_ADMIN')", output: NotificationProviderOutput::class, provider: NotificationProviderCatalogProvider::class),
        new Post(uriTemplate: '/admin/notification-providers', security: "is_granted('ROLE_SUPER_ADMIN')", input: NotificationProviderInput::class, output: NotificationProviderOutput::class, processor: NotificationProviderCatalogProcessor::class),
        new Get(uriTemplate: '/admin/notification-providers/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", output: NotificationProviderOutput::class, provider: NotificationProviderCatalogProvider::class),
        new Patch(uriTemplate: '/admin/notification-providers/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", input: NotificationProviderInput::class, output: NotificationProviderOutput::class, processor: NotificationProviderCatalogProcessor::class),
        new Delete(uriTemplate: '/admin/notification-providers/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", processor: NotificationProviderCatalogProcessor::class),
    ],
)]
final class NotificationProviderResource
{
    public ?string $id = null;
}
