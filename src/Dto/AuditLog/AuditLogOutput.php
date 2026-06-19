<?php

namespace App\Dto\AuditLog;

final class AuditLogOutput
{
    public function __construct(
        public string $id,
        public string $actorEmail,
        public string $actorRole,
        public ?string $boutiqueId,
        public string $action,
        public string $resourceType,
        public ?string $resourceId,
        public ?array $details,
        public ?string $ipAddress,
        public string $createdAt,
    ) {
    }
}
