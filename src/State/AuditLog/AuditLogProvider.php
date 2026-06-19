<?php

namespace App\State\AuditLog;

use App\Dto\AuditLog\AuditLogOutput;
use App\Entity\AuditLog;
use App\Repository\AuditLogRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class AuditLogProvider implements ProviderInterface
{
    public function __construct(
        private AuditLogRepository $logs,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AuditLogOutput
    {
        $log = $this->logs->find($uriVariables['id'] ?? null);
        if (!$log instanceof AuditLog) {
            return null;
        }

        return $this->toOutput($log);
    }

    /** @return list<AuditLogOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $logs = $this->logs->findByBoutique(null);

        return array_map($this->toOutput(...), $logs);
    }

    private function toOutput(AuditLog $log): AuditLogOutput
    {
        return new AuditLogOutput(
            id: (string) $log->getId(),
            actorEmail: $log->getActorEmail(),
            actorRole: $log->getActorRole(),
            boutiqueId: $log->getBoutique() ? (string) $log->getBoutique()->getId() : null,
            action: $log->getAction(),
            resourceType: $log->getResourceType(),
            resourceId: $log->getResourceId(),
            details: $log->getDetails(),
            ipAddress: $log->getIpAddress(),
            createdAt: $log->getCreatedAt()->format('c'),
        );
    }
}
