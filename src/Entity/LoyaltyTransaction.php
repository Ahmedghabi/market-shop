<?php

namespace App\Entity;

use App\Enum\LoyaltyTransactionType;
use App\Repository\LoyaltyTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Immutable ledger entry for every loyalty points movement (gain, redemption,
 * expiration, cancellation, correction). `remainingPoints` tracks how many
 * points from an Earn batch are still unconsumed, enabling FIFO redemption,
 * proportional cancellation/refund reversal, and per-batch expiry.
 */
#[ORM\Entity(repositoryClass: LoyaltyTransactionRepository::class)]
#[ORM\Table(name: 'loyalty_transaction')]
#[ORM\Index(name: 'idx_loyalty_transaction_account', columns: ['customer_loyalty_id'])]
#[ORM\Index(name: 'idx_loyalty_transaction_boutique', columns: ['boutique_id'])]
#[ORM\Index(name: 'idx_loyalty_transaction_order', columns: ['order_id'])]
#[ORM\Index(name: 'idx_loyalty_transaction_expires', columns: ['expires_at'])]
class LoyaltyTransaction extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: CustomerLoyalty::class, inversedBy: 'transactions')]
        #[ORM\JoinColumn(name: 'customer_loyalty_id', nullable: false, onDelete: 'CASCADE')]
        private CustomerLoyalty $customerLoyalty,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 32, enumType: LoyaltyTransactionType::class)]
        private LoyaltyTransactionType $type,
        #[ORM\Column]
        private int $points,
        #[ORM\Column(nullable: true)]
        private ?int $remainingPoints = null,
        #[ORM\Column(nullable: true)]
        private ?int $discountCents = null,
        #[ORM\ManyToOne(targetEntity: Order::class)]
        #[ORM\JoinColumn(name: 'order_id', nullable: true, onDelete: 'SET NULL')]
        private ?Order $order = null,
        #[ORM\ManyToOne(targetEntity: LoyaltyRule::class)]
        #[ORM\JoinColumn(name: 'rule_id', nullable: true, onDelete: 'SET NULL')]
        private ?LoyaltyRule $rule = null,
        #[ORM\ManyToOne(targetEntity: LoyaltyReward::class)]
        #[ORM\JoinColumn(name: 'reward_id', nullable: true, onDelete: 'SET NULL')]
        private ?LoyaltyReward $reward = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $reason = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $expiresAt = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getCustomerLoyalty(): CustomerLoyalty
    {
        return $this->customerLoyalty;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getType(): LoyaltyTransactionType
    {
        return $this->type;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getRemainingPoints(): ?int
    {
        return $this->remainingPoints;
    }

    public function setRemainingPoints(?int $remainingPoints): void
    {
        $this->remainingPoints = $remainingPoints;
    }

    public function consumePoints(int $amount): int
    {
        $available = $this->remainingPoints ?? 0;
        $consumed = min($available, $amount);
        $this->remainingPoints = $available - $consumed;

        return $consumed;
    }

    public function getDiscountCents(): ?int
    {
        return $this->discountCents;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getRule(): ?LoyaltyRule
    {
        return $this->rule;
    }

    public function getReward(): ?LoyaltyReward
    {
        return $this->reward;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
