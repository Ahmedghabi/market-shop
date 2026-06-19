<?php

namespace App\Entity;

use App\Repository\ShopModuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopModuleRepository::class)]
#[ORM\Table(name: 'shop_module')]
#[ORM\UniqueConstraint(name: 'uniq_shop_module', columns: ['boutique_id', 'module_id'])]
class ShopModule extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: SubscriptionPlanModule::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlanModule $module,
        #[ORM\Column]
        private bool $isEnabled = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
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
