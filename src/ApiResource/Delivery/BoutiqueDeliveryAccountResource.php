<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Delivery\BoutiqueDeliveryAccountInput;
use App\Dto\Delivery\BoutiqueDeliveryAccountOutput;
use App\State\Delivery\BoutiqueDeliveryAccountProcessor;
use App\State\Delivery\BoutiqueDeliveryAccountProvider;

const BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'BoutiqueDeliveryAccount',
    operations: [
        new GetCollection(
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: BoutiqueDeliveryAccountOutput::class,
            provider: BoutiqueDeliveryAccountProvider::class,
        ),
        new Post(
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES,
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: BoutiqueDeliveryAccountInput::class,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts/{id}',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: BoutiqueDeliveryAccountOutput::class,
            provider: BoutiqueDeliveryAccountProvider::class,
        ),
        new Patch(
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts/{id}',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: BoutiqueDeliveryAccountInput::class,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Delete(
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts/{id}',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Post(
            name: 'verify_delivery_account',
            uriTemplate: '/boutiques/{boutiqueId}/delivery-accounts/{id}/verify',
            uriVariables: BOUTIQUE_DELIVERY_ACCOUNT_URI_VARIABLES + ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: false,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
    ],
)]
final class BoutiqueDeliveryAccountResource
{
    public ?string $id = null;
    public ?string $deliveryCompanyId = null;
    public ?string $deliveryCompanyName = null;
    public bool $isVerified = false;
    public ?string $verifiedAt = null;
    public ?string $lastError = null;
    public bool $isActive = true;
    public ?string $createdAt = null;
}
