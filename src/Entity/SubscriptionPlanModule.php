<?php

namespace App\Entity;

use App\Repository\SubscriptionPlanModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionPlanModuleRepository::class)]
#[ORM\Table(name: 'subscription_plan_module')]
class SubscriptionPlanModule extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 64, unique: true)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $category = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $icon = null,
        #[ORM\Column]
        private bool $isCore = false,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function isCore(): bool
    {
        return $this->isCore;
    }

    public function setIsCore(bool $isCore): void
    {
        $this->isCore = $isCore;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
