<?php

namespace App\ApiResource\Subscription;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\SubscriptionRequest\SubscriptionRequestOutput;
use App\State\Subscription\SubscriptionRenewProcessor;

#[ApiResource(
    shortName: 'SubscriptionRenew',
    operations: [
        new Post(
            uriTemplate: '/subscription/renew',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            read: false,
            input: false,
            output: SubscriptionRequestOutput::class,
            processor: SubscriptionRenewProcessor::class,
        ),
    ],
)]
final class SubscriptionRenewResource
{
}
