<?php

namespace App\Entity;

use App\Enum\Subscription\SubscriptionRequestStatus;
use App\Repository\SubscriptionRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRequestRepository::class)]
#[ORM\Table(name: 'subscription_request')]
class SubscriptionRequest extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: SubscriptionPlan::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SubscriptionPlan $subscriptionPlan,
        #[ORM\Column(length: 32, enumType: SubscriptionRequestStatus::class)]
        private SubscriptionRequestStatus $status = SubscriptionRequestStatus::Pending,
        #[ORM\Column]
        private \DateTimeImmutable $requestedAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $approvedAt = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $approvedBy = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getSubscriptionPlan(): SubscriptionPlan
    {
        return $this->subscriptionPlan;
    }

    public function getStatus(): SubscriptionRequestStatus
    {
        return $this->status;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getApprovedBy(): ?string
    {
        return $this->approvedBy;
    }

    public function approve(string $approvedBy): void
    {
        $this->status = SubscriptionRequestStatus::Approved;
        $this->approvedAt = new \DateTimeImmutable();
        $this->approvedBy = $approvedBy;
    }

    public function reject(): void
    {
        $this->status = SubscriptionRequestStatus::Rejected;
    }
}
