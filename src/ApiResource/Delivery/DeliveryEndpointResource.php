<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Delivery\DeliveryEndpointInput;
use App\Dto\Delivery\DeliveryEndpointOutput;
use App\State\Delivery\DeliveryEndpointProcessor;
use App\State\Delivery\DeliveryEndpointProvider;

#[ApiResource(
    shortName: 'DeliveryEndpoint',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/delivery-companies/{companyId}/endpoints',
            uriVariables: ['companyId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: DeliveryEndpointOutput::class,
            provider: DeliveryEndpointProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/delivery-companies/{companyId}/endpoints',
            uriVariables: ['companyId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: DeliveryEndpointInput::class,
            output: DeliveryEndpointOutput::class,
            processor: DeliveryEndpointProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/delivery-endpoints/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: DeliveryEndpointOutput::class,
            provider: DeliveryEndpointProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/delivery-endpoints/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: DeliveryEndpointInput::class,
            output: DeliveryEndpointOutput::class,
            processor: DeliveryEndpointProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/delivery-endpoints/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: DeliveryEndpointProcessor::class,
        ),
    ],
)]
final class DeliveryEndpointResource
{
    public ?string $id = null;
    public ?string $companyId = null;
    public ?string $type = null;
    public ?string $name = null;
    public ?string $url = null;
    public ?string $httpMethod = null;
    public array $headers = [];
    public ?string $responseType = null;
    public bool $isActive = true;
}
