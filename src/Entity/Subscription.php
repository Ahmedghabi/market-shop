<?php

namespace App\Entity;

use App\Enum\PlanType;
use App\Enum\SubscriptionStatus;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscription')]
class Subscription extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'subscriptions')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 32, enumType: PlanType::class)]
        private PlanType $plan,
        #[ORM\Column(length: 32, enumType: SubscriptionStatus::class)]
        private SubscriptionStatus $status = SubscriptionStatus::Pending,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $startDate = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $endDate = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $acceptedBy = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $acceptedAt = null,
        #[ORM\ManyToOne(targetEntity: SubscriptionPlan::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?SubscriptionPlan $subscriptionPlan = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getPlan(): PlanType
    {
        return $this->plan;
    }

    public function getStatus(): SubscriptionStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getAcceptedBy(): ?string
    {
        return $this->acceptedBy;
    }

    public function getAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->acceptedAt;
    }

    public function activate(string $acceptedBy): void
    {
        $now = new \DateTimeImmutable();
        $this->status = SubscriptionStatus::Active;
        $this->startDate = $now;
        $this->endDate = $this->plan->durationMonths() > 0
            ? $now->modify(sprintf('+%d months', $this->plan->durationMonths()))
            : null;
        $this->acceptedBy = $acceptedBy;
        $this->acceptedAt = $now;
    }

    public function reject(): void
    {
        $this->status = SubscriptionStatus::Rejected;
    }

    public function getSubscriptionPlan(): ?SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?SubscriptionPlan $plan): void
    {
        $this->subscriptionPlan = $plan;
    }

    public function markAsExpired(): void
    {
        $this->status = SubscriptionStatus::Expired;
    }
}
