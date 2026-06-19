<?php

namespace App\ApiResource\Permission;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Permission\PermissionInput;
use App\Dto\Permission\PermissionOutput;
use App\State\Permission\PermissionProcessor;
use App\State\Permission\PermissionProvider;

#[ApiResource(
    shortName: 'Permission',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/permissions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: PermissionOutput::class,
            provider: PermissionProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/permissions',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            read: false,
            input: PermissionInput::class,
            output: PermissionOutput::class,
            processor: PermissionProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/permissions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: PermissionOutput::class,
            provider: PermissionProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/permissions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: PermissionInput::class,
            output: PermissionOutput::class,
            processor: PermissionProcessor::class,
        ),
        new Delete(
            uriTemplate: '/admin/permissions/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            processor: PermissionProcessor::class,
        ),
    ],
)]
final class PermissionResource
{
    public ?string $id = null;
    public ?string $code = null;
    public ?string $name = null;
    public ?string $module = null;
    public ?string $description = null;
}
