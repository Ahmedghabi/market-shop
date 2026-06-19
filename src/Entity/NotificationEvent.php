<?php

namespace App\Entity;

use App\Repository\NotificationEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationEventRepository::class)]
#[ORM\Table(name: 'notification_event')]
class NotificationEvent extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 80, unique: true)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column]
        private bool $isActive = true,
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
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
