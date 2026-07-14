<?php

namespace App\ApiResource\DeliveryRule;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\DeliveryRule\DeliveryRuleProcessor;
use App\State\DeliveryRule\DeliveryRuleProvider;
use App\Dto\DeliveryRule\DeliveryRuleInput;
use App\Dto\DeliveryRule\DeliveryRuleOutput;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/delivery-rules/{id}',
            output: DeliveryRuleOutput::class,
            provider: DeliveryRuleProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/delivery-rules',
            output: DeliveryRuleOutput::class,
            provider: DeliveryRuleProvider::class,
        ),
        new Post(
            uriTemplate: '/delivery-rules',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: DeliveryRuleInput::class,
            output: DeliveryRuleOutput::class,
            read: false,
            processor: DeliveryRuleProcessor::class,
        ),
        new Put(
            uriTemplate: '/delivery-rules/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: DeliveryRuleInput::class,
            output: DeliveryRuleOutput::class,
            read: false,
            processor: DeliveryRuleProcessor::class,
        ),
    ],
)]
final class DeliveryRuleResource
{
}
