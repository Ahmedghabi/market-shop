<?php

namespace App\ApiResource\Delivery;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Delivery\DeliveryCompanyInput;
use App\Dto\Delivery\DeliveryCompanyOutput;
use App\State\Delivery\DeliveryCompanyProcessor;
use App\State\Delivery\DeliveryCompanyProvider;

#[ApiResource(
    shortName: 'DeliveryCompany',
    operations: [
        new GetCollection(
            uriTemplate: '/delivery/companies',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: DeliveryCompanyOutput::class,
            provider: DeliveryCompanyProvider::class,
        ),
        new GetCollection(
            name: 'admin_list_delivery_companies',
            uriTemplate: '/admin/delivery-companies',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: DeliveryCompanyOutput::class,
            provider: DeliveryCompanyProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/delivery-companies',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: DeliveryCompanyInput::class,
            output: DeliveryCompanyOutput::class,
            processor: DeliveryCompanyProcessor::class,
        ),
        new Get(
            uriTemplate: '/delivery/companies/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: DeliveryCompanyOutput::class,
            provider: DeliveryCompanyProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/delivery-companies/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: DeliveryCompanyInput::class,
            output: DeliveryCompanyOutput::class,
            processor: DeliveryCompanyProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/delivery-companies/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: DeliveryCompanyProcessor::class,
        ),
    ],
)]
final class DeliveryCompanyResource
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $baseUrl = null;
    public ?string $provider = null;
    public ?string $authType = null;
    public array $authConfig = [];
    public array $mappingConfig = [];
    public array $parametersConfig = [];
    public ?string $logoUrl = null;
    public ?string $description = null;
    public bool $isActive = true;
}
