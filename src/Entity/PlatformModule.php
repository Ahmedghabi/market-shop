<?php

namespace App\Entity;

use App\Repository\PlatformModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatformModuleRepository::class)]
#[ORM\Table(name: 'platform_module')]
#[ORM\UniqueConstraint(name: 'uniq_platform_module', columns: ['module_id'])]
class PlatformModule extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: SubscriptionPlanModule::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlanModule $module,
        #[ORM\Column]
        private bool $isEnabled = true,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $reasonDisabled = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getModule(): SubscriptionPlanModule
    {
        return $this->module;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
        $this->touch();
    }

    public function getReasonDisabled(): ?string
    {
        return $this->reasonDisabled;
    }

    public function setReasonDisabled(?string $reason): void
    {
        $this->reasonDisabled = $reason;
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
