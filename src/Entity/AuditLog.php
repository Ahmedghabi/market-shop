<?php

namespace App\Entity;

use App\Repository\AuditLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_log')]
#[ORM\Index(name: 'idx_audit_log_created', columns: ['created_at'])]
class AuditLog extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 180)]
        private string $actorEmail,
        #[ORM\Column(length: 32)]
        private string $actorRole,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Boutique $boutique = null,
        #[ORM\Column(length: 80)]
        private string $action,
        #[ORM\Column(length: 80)]
        private string $resourceType,
        #[ORM\Column(length: 36, nullable: true)]
        private ?string $resourceId = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $details = null,
        #[ORM\Column(length: 45, nullable: true)]
        private ?string $ipAddress = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getActorEmail(): string
    {
        return $this->actorEmail;
    }

    public function getActorRole(): string
    {
        return $this->actorRole;
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
