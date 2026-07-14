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
            uriTemplate: '/delivery-accounts',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: BoutiqueDeliveryAccountOutput::class,
            provider: BoutiqueDeliveryAccountProvider::class,
        ),
        new Post(
            uriTemplate: '/delivery-accounts',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: BoutiqueDeliveryAccountInput::class,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Get(
            uriTemplate: '/delivery-accounts/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: BoutiqueDeliveryAccountOutput::class,
            provider: BoutiqueDeliveryAccountProvider::class,
        ),
        new Patch(
            uriTemplate: '/delivery-accounts/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: BoutiqueDeliveryAccountInput::class,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Delete(
            uriTemplate: '/delivery-accounts/{id}',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Post(
            name: 'verify_delivery_account',
            uriTemplate: '/delivery-accounts/{id}/verify',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            input: false,
            output: BoutiqueDeliveryAccountOutput::class,
            processor: BoutiqueDeliveryAccountProcessor::class,
        ),
        new Post(
            name: 'set_default_delivery_account',
            uriTemplate: '/delivery-accounts/{id}/set-default',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
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
    public bool $isDefault = false;
    public bool $hasApiKey = false;
    public bool $hasToken = false;
    public bool $hasSecret = false;
    public ?string $customBaseUrl = null;
    public ?string $createdAt = null;
}
