<?php

namespace App\ApiResource\Payment;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Payment\ShopPaymentMethodInput;
use App\Dto\Payment\ShopPaymentMethodOutput;
use App\State\Payment\ShopPaymentMethodProcessor;
use App\State\Payment\ShopPaymentMethodProvider;

#[ApiResource(
    shortName: 'ShopPaymentMethod',
    operations: [
        new GetCollection(uriTemplate: '/boutiques/{boutiqueId}/payment-methods', uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], output: ShopPaymentMethodOutput::class, provider: ShopPaymentMethodProvider::class),
        new Post(uriTemplate: '/boutiques/{boutiqueId}/payment-methods', uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", input: ShopPaymentMethodInput::class, output: ShopPaymentMethodOutput::class, processor: ShopPaymentMethodProcessor::class),
        new Get(uriTemplate: '/boutiques/{boutiqueId}/payment-methods/{id}', uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'), 'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], output: ShopPaymentMethodOutput::class, provider: ShopPaymentMethodProvider::class),
        new Patch(uriTemplate: '/boutiques/{boutiqueId}/payment-methods/{id}', uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'), 'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", input: ShopPaymentMethodInput::class, output: ShopPaymentMethodOutput::class, processor: ShopPaymentMethodProcessor::class),
        new Delete(uriTemplate: '/boutiques/{boutiqueId}/payment-methods/{id}', uriVariables: ['boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'), 'id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')], security: "is_granted('ROLE_BOUTIQUE_ADMIN')", processor: ShopPaymentMethodProcessor::class),
    ],
)]
final class ShopPaymentMethodResource
{
    public ?string $id = null;
}
