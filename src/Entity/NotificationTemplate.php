<?php

namespace App\Entity;

use App\Enum\NotificationChannel;
use App\Repository\NotificationTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationTemplateRepository::class)]
#[ORM\Table(name: 'notification_template')]
#[ORM\UniqueConstraint(name: 'uniq_notification_template', columns: ['boutique_id', 'event_code', 'channel'])]
class NotificationTemplate extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?Boutique $boutique,
        #[ORM\Column(length: 80)]
        private string $eventCode,
        #[ORM\Column(length: 16, enumType: NotificationChannel::class)]
        private NotificationChannel $channel,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $subject,
        #[ORM\Column(type: 'text')]
        private string $content,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function getEventCode(): string
    {
        return $this->eventCode;
    }

    public function setEventCode(string $eventCode): void
    {
        $this->eventCode = $eventCode;
        $this->touch();
    }

    public function getChannel(): NotificationChannel
    {
        return $this->channel;
    }

    public function setChannel(NotificationChannel $channel): void
    {
        $this->channel = $channel;
        $this->touch();
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
        $this->touch();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
