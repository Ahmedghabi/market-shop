<?php

namespace App\ApiResource\DeliveryRule;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\DeliveryRule\DeliveryRuleProcessor;
use App\State\DeliveryRule\DeliveryRuleProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/delivery-rules/{id}',
            provider: DeliveryRuleProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/delivery-rules',
            provider: DeliveryRuleProvider::class,
        ),
        new Post(
            uriTemplate: '/delivery-rules',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: DeliveryRuleProcessor::class,
        ),
        new Put(
            uriTemplate: '/delivery-rules/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: DeliveryRuleProcessor::class,
        ),
    ],
)]
final class DeliveryRuleResource
{
}
