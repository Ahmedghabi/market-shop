<?php

namespace App\ApiResource\Subscription;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Subscription\SubscriptionInput;
use App\Dto\Subscription\SubscriptionOutput;
use App\State\Subscription\SubscriptionProcessor;
use App\State\Subscription\SubscriptionProvider;

const BOUTIQUE_SUBSCRIPTION_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'Subscription',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/subscriptions',
            uriVariables: BOUTIQUE_SUBSCRIPTION_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: SubscriptionOutput::class,
            provider: SubscriptionProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/subscriptions',
            uriVariables: BOUTIQUE_SUBSCRIPTION_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: SubscriptionInput::class,
            output: SubscriptionOutput::class,
            processor: SubscriptionProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/subscriptions/{id}',
            uriVariables: BOUTIQUE_SUBSCRIPTION_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: SubscriptionOutput::class,
            provider: SubscriptionProvider::class,
        ),
        new Patch(
            name: 'accept_subscription',
            uriTemplate: '/boutiques/{boutiqueId}/subscriptions/{id}/accept',
            uriVariables: BOUTIQUE_SUBSCRIPTION_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: false,
            output: SubscriptionOutput::class,
            processor: SubscriptionProcessor::class,
        ),
        new Patch(
            name: 'reject_subscription',
            uriTemplate: '/boutiques/{boutiqueId}/subscriptions/{id}/reject',
            uriVariables: BOUTIQUE_SUBSCRIPTION_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: false,
            output: SubscriptionOutput::class,
            processor: SubscriptionProcessor::class,
        ),
    ],
)]
final class SubscriptionResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public string $plan;
    public string $status = 'pending';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $acceptedBy = null;
    public ?string $acceptedAt = null;
    public ?string $createdAt = null;
    public int $priceCents = 0;
}
