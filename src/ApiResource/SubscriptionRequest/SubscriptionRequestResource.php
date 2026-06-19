<?php

namespace App\ApiResource\SubscriptionRequest;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\SubscriptionRequest\SubscriptionRequestInput;
use App\Dto\SubscriptionRequest\SubscriptionRequestOutput;
use App\State\SubscriptionRequest\SubscriptionRequestProcessor;
use App\State\SubscriptionRequest\SubscriptionRequestProvider;

const BOUTIQUE_REQUEST_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'SubscriptionRequest',
    operations: [
        new GetCollection(
            uriTemplate: '/subscription-requests',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: SubscriptionRequestOutput::class,
            provider: SubscriptionRequestProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/admin/subscription-requests',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SubscriptionRequestOutput::class,
            provider: SubscriptionRequestProvider::class,
        ),
        new Post(
            uriTemplate: '/subscription-requests',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: SubscriptionRequestInput::class,
            output: SubscriptionRequestOutput::class,
            processor: SubscriptionRequestProcessor::class,
        ),
        new Get(
            uriTemplate: '/subscription-requests/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: SubscriptionRequestOutput::class,
            provider: SubscriptionRequestProvider::class,
        ),
        new Patch(
            name: 'approve_subscription_request',
            uriTemplate: '/admin/subscription-requests/{id}/approve',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: false,
            output: SubscriptionRequestOutput::class,
            processor: SubscriptionRequestProcessor::class,
        ),
        new Patch(
            name: 'reject_subscription_request',
            uriTemplate: '/admin/subscription-requests/{id}/reject',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: false,
            output: SubscriptionRequestOutput::class,
            processor: SubscriptionRequestProcessor::class,
        ),
    ],
)]
final class SubscriptionRequestResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public ?string $subscriptionPlanId = null;
    public ?string $subscriptionPlanName = null;
    public string $status = 'pending';
    public ?string $requestedAt = null;
    public ?string $approvedAt = null;
    public ?string $approvedBy = null;
}
