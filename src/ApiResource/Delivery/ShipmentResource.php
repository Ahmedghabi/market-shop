<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use App\Dto\Delivery\ShipmentCreateInput;
use App\Dto\Delivery\ShipmentOutput;
use App\Dto\Delivery\ShipmentQueueOutput;
use App\State\Delivery\ShipmentProcessor;
use App\State\Delivery\ShipmentProvider;

#[ApiResource(
    shortName: 'Shipment',
    operations: [
        new GetCollection(
            uriTemplate: '/delivery/shipments',
            security: "is_granted('ROLE_CAISSIER')",
            output: ShipmentOutput::class,
            provider: ShipmentProvider::class,
        ),
        new GetCollection(
            name: 'admin_list_shipments',
            uriTemplate: '/admin/delivery/shipments',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: ShipmentOutput::class,
            provider: ShipmentProvider::class,
        ),
        new Get(
            uriTemplate: '/delivery/shipments/{id}',
            security: "is_granted('ROLE_CAISSIER')",
            output: ShipmentOutput::class,
            provider: ShipmentProvider::class,
        ),
        new Post(
            name: 'create_shipment',
            uriTemplate: '/delivery/shipments',
            security: "is_granted('ROLE_CAISSIER')",
            input: ShipmentCreateInput::class,
            output: ShipmentOutput::class,
            processor: ShipmentProcessor::class,
        ),
        new Post(
            name: 'queue_shipment',
            uriTemplate: '/delivery/shipments/queue',
            security: "is_granted('ROLE_CAISSIER')",
            input: ShipmentCreateInput::class,
            output: ShipmentQueueOutput::class,
            processor: ShipmentProcessor::class,
        ),
        new Post(
            name: 'cancel_shipment',
            uriTemplate: '/delivery/shipments/{id}/cancel',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_CAISSIER')",
            input: false,
            output: ShipmentOutput::class,
            processor: ShipmentProcessor::class,
        ),
        new Post(
            name: 'track_shipment',
            uriTemplate: '/delivery/shipments/{id}/track',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_CAISSIER')",
            input: false,
            output: ShipmentOutput::class,
            processor: ShipmentProcessor::class,
        ),
        new Post(
            name: 'label_shipment',
            uriTemplate: '/delivery/shipments/{id}/label',
            uriVariables: ['id' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_CAISSIER')",
            input: false,
            output: ShipmentOutput::class,
            processor: ShipmentProcessor::class,
        ),
    ],
)]
final class ShipmentResource
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $orderId = null;
    public ?string $deliveryCompanyId = null;
    public ?string $deliveryCompanyName = null;
    public ?string $credentialId = null;
    public ?string $status = null;
    public ?string $trackingNumber = null;
    public ?string $labelUrl = null;
    public ?int $costCents = null;
    public ?string $errorMessage = null;
    public ?string $createdAt = null;
    public ?string $sentAt = null;
    public ?string $updatedAt = null;
}
