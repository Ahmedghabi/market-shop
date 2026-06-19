<?php

namespace App\Entity;

use App\Repository\SubscriptionModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionModuleRepository::class)]
#[ORM\Table(name: 'subscription_module')]
#[ORM\UniqueConstraint(name: 'uniq_subscription_module', columns: ['plan_id', 'module_id'])]
class SubscriptionModule extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: SubscriptionPlan::class, inversedBy: 'subscriptionModules')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlan $plan,
        #[ORM\ManyToOne(targetEntity: SubscriptionPlanModule::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlanModule $module,
        #[ORM\Column]
        private bool $isAllowed = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getPlan(): SubscriptionPlan
    {
        return $this->plan;
    }

    public function getModule(): SubscriptionPlanModule
    {
        return $this->module;
    }

    public function isAllowed(): bool
    {
        return $this->isAllowed;
    }

    public function setAllowed(bool $isAllowed): void
    {
        $this->isAllowed = $isAllowed;
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
