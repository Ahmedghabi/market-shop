<?php

namespace App\ApiResource\Notification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Notification\NotificationTemplateInput;
use App\Dto\Notification\NotificationTemplateOutput;
use App\State\Notification\NotificationTemplateProcessor;
use App\State\Notification\NotificationTemplateProvider;

#[ApiResource(
    shortName: 'NotificationTemplate',
    operations: [
        new GetCollection(uriTemplate: '/admin/notification-templates', security: "is_granted('ROLE_SUPER_ADMIN')", output: NotificationTemplateOutput::class, provider: NotificationTemplateProvider::class),
        new GetCollection(uriTemplate: '/notification-templates', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", output: NotificationTemplateOutput::class, provider: NotificationTemplateProvider::class),
        new Post(uriTemplate: '/notification-templates', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", input: NotificationTemplateInput::class, output: NotificationTemplateOutput::class, processor: NotificationTemplateProcessor::class),
        new Get(uriTemplate: '/notification-templates/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')", output: NotificationTemplateOutput::class, provider: NotificationTemplateProvider::class),
        new Patch(uriTemplate: '/notification-templates/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')", input: NotificationTemplateInput::class, output: NotificationTemplateOutput::class, processor: NotificationTemplateProcessor::class),
        new Delete(uriTemplate: '/notification-templates/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')", processor: NotificationTemplateProcessor::class),
    ],
)]
final class NotificationTemplateResource
{
    public ?string $id = null;
}
