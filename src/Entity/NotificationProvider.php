<?php

namespace App\Entity;

use App\Enum\NotificationChannel;
use App\Repository\NotificationProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationProviderRepository::class)]
#[ORM\Table(name: 'notification_provider')]
class NotificationProvider extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 80, unique: true)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 16, enumType: NotificationChannel::class)]
        private NotificationChannel $type,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getType(): NotificationChannel
    {
        return $this->type;
    }

    public function setType(NotificationChannel $type): void
    {
        $this->type = $type;
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
