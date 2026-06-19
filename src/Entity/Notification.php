<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\Table(name: 'notification')]
class Notification extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $recipientIdentifier,
        #[ORM\Column(length: 80)]
        private string $type,
        #[ORM\Column(length: 180)]
        private string $title,
        #[ORM\Column(type: 'text')]
        private string $message,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?Boutique $boutique = null,
        #[ORM\Column]
        private bool $read = false,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getRecipientIdentifier(): ?string
    {
        return $this->recipientIdentifier;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function markAsRead(): void
    {
        $this->read = true;
    }
}
