<?php

namespace App\ApiResource\CustomerNotification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\CustomerNotification\CustomerNotificationProvider;
use App\State\CustomerNotification\CustomerNotificationProcessor;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/me/notifications',
            security: "is_granted('ROLE_CUSTOMER')",
            provider: CustomerNotificationProvider::class,
        ),
        new Post(
            uriTemplate: '/me/notifications/{id}/read',
            security: "is_granted('ROLE_CUSTOMER')",
            processor: CustomerNotificationProcessor::class,
        ),
        new Post(
            uriTemplate: '/me/notifications/read-all',
            security: "is_granted('ROLE_CUSTOMER')",
            processor: CustomerNotificationProcessor::class,
            name: 'notification_read_all',
        ),
    ],
)]
final class CustomerNotificationResource
{
}
