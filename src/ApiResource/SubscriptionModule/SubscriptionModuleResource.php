<?php

namespace App\ApiResource\SubscriptionModule;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\SubscriptionModule\SubscriptionModuleInput;
use App\Dto\SubscriptionModule\SubscriptionModuleOutput;
use App\State\SubscriptionModule\SubscriptionModuleProcessor;
use App\State\SubscriptionModule\SubscriptionModuleProvider;

#[ApiResource(
    shortName: 'SubscriptionModule',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/subscription-plans/{planId}/modules',
            uriVariables: ['planId' => new \ApiPlatform\Metadata\Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SubscriptionModuleOutput::class,
            provider: SubscriptionModuleProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/subscription-modules',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: SubscriptionModuleInput::class,
            output: SubscriptionModuleOutput::class,
            processor: SubscriptionModuleProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/subscription-modules/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SubscriptionModuleOutput::class,
            provider: SubscriptionModuleProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/subscription-modules/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: SubscriptionModuleInput::class,
            output: SubscriptionModuleOutput::class,
            processor: SubscriptionModuleProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/subscription-modules/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: SubscriptionModuleProcessor::class,
        ),
    ],
)]
final class SubscriptionModuleResource
{
    public ?string $id = null;
    public ?string $planId = null;
    public ?string $moduleId = null;
    public bool $isAllowed = true;
}
