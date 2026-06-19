<?php

namespace App\Service\Audit;

use App\Entity\AuditLog;
use App\Entity\Boutique;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AuditLogService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function log(
        string $actorEmail,
        string $actorRole,
        string $action,
        string $resourceType,
        ?string $resourceId = null,
        ?array $details = null,
        ?string $ipAddress = null,
        ?string $boutiqueId = null,
    ): void {
        $boutique = null;
        if (null !== $boutiqueId) {
            $boutique = $this->em->find(Boutique::class, $boutiqueId);
        }

        $log = new AuditLog(
            actorEmail: $actorEmail,
            actorRole: $actorRole,
            boutique: $boutique,
            action: $action,
            resourceType: $resourceType,
            resourceId: $resourceId,
            details: $details,
            ipAddress: $ipAddress,
        );

        $this->em->persist($log);
        $this->em->flush();
    }
}
