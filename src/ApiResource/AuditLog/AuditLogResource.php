<?php

namespace App\ApiResource\AuditLog;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\AuditLog\AuditLogProvider;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/admin/audit-logs/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            provider: AuditLogProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/admin/audit-logs',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            provider: AuditLogProvider::class,
        ),
    ],
)]
final class AuditLogResource
{
}
