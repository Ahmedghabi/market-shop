<?php

namespace App\ApiResource\Extension;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Extension\ExtensionInput;
use App\Dto\Extension\ExtensionOutput;
use App\State\Extension\ExtensionProcessor;
use App\State\Extension\ExtensionProvider;

#[ApiResource(
    shortName: 'Extension',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/extensions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: ExtensionOutput::class,
            provider: ExtensionProvider::class,
        ),
        new GetCollection(
            name: 'available_extensions',
            uriTemplate: '/extensions/available',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            output: ExtensionOutput::class,
            provider: ExtensionProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/extensions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: ExtensionInput::class,
            output: ExtensionOutput::class,
            processor: ExtensionProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/extensions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: ExtensionOutput::class,
            provider: ExtensionProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/extensions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: ExtensionInput::class,
            output: ExtensionOutput::class,
            processor: ExtensionProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/extensions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: ExtensionProcessor::class,
        ),
    ],
)]
final class ExtensionResource
{
    public ?string $id = null;
    public string $code;
    public string $name;
    public ?string $description = null;
    public string $type;
    public ?string $targetCode = null;
    public ?int $value = null;
    public int $priceTnd = 0;
    public ?int $durationMonths = null;
    public bool $requiresValidation = true;
    public bool $isActive = true;
    public ?string $icon = null;
    public bool $isFree = false;
    public bool $isPermanent = true;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
    public bool $alreadyActive = false;
    public ?string $pendingRequestId = null;
    public ?string $pendingRequestStatus = null;
}
