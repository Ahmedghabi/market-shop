<?php

namespace App\Entity;

use App\Repository\PlanQuotaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanQuotaRepository::class)]
#[ORM\Table(name: 'plan_quota')]
#[ORM\UniqueConstraint(name: 'uniq_plan_quota', columns: ['plan_id', 'quota_id'])]
class PlanQuota extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: SubscriptionPlan::class, inversedBy: 'planQuotas')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlan $plan,
        #[ORM\ManyToOne(targetEntity: QuotaDefinition::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private QuotaDefinition $quota,
        /**
         * Null means unlimited for this plan.
         */
        #[ORM\Column(nullable: true)]
        private ?int $limitValue = null,
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

    public function getQuota(): QuotaDefinition
    {
        return $this->quota;
    }

    public function getLimitValue(): ?int
    {
        return $this->limitValue;
    }

    public function setLimitValue(?int $limitValue): void
    {
        $this->limitValue = $limitValue;
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
