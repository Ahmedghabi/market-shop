<?php

namespace App\ApiResource\QuotaDefinition;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\QuotaDefinition\QuotaDefinitionInput;
use App\Dto\QuotaDefinition\QuotaDefinitionOutput;
use App\State\QuotaDefinition\QuotaDefinitionProcessor;
use App\State\QuotaDefinition\QuotaDefinitionProvider;

#[ApiResource(
    shortName: 'QuotaDefinition',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/quota-definitions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: QuotaDefinitionOutput::class,
            provider: QuotaDefinitionProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/quota-definitions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: QuotaDefinitionInput::class,
            output: QuotaDefinitionOutput::class,
            processor: QuotaDefinitionProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/quota-definitions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: QuotaDefinitionOutput::class,
            provider: QuotaDefinitionProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/quota-definitions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: QuotaDefinitionInput::class,
            output: QuotaDefinitionOutput::class,
            processor: QuotaDefinitionProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/quota-definitions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: QuotaDefinitionProcessor::class,
        ),
    ],
)]
final class QuotaDefinitionResource
{
    public ?string $id = null;
    public string $code;
    public string $name;
    public ?string $description = null;
    public ?string $unit = null;
    public ?string $category = null;
    public ?string $icon = null;
    public bool $isActive = true;
    public ?string $createdAt = null;
}
