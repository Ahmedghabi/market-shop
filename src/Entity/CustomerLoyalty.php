<?php

namespace App\Entity;

use App\Repository\CustomerLoyaltyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerLoyaltyRepository::class)]
#[ORM\Table(name: 'customer_loyalty')]
#[ORM\UniqueConstraint(name: 'uniq_customer_loyalty', columns: ['customer_id', 'boutique_id'])]
class CustomerLoyalty extends AbstractEntity
{
    /** @var Collection<int, LoyaltyTransaction> */
    #[ORM\OneToMany(mappedBy: 'customerLoyalty', targetEntity: LoyaltyTransaction::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $transactions;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'loyaltyRecords')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Customer $customer,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column]
        private int $pointsBalance = 0,
        #[ORM\Column]
        private int $totalEarned = 0,
        #[ORM\Column]
        private int $totalUsed = 0,
    ) {
        parent::__construct();
        $this->transactions = new ArrayCollection();
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getPointsBalance(): int
    {
        return $this->pointsBalance;
    }

    public function getTotalEarned(): int
    {
        return $this->totalEarned;
    }

    public function getTotalUsed(): int
    {
        return $this->totalUsed;
    }

    public function addPoints(int $points): void
    {
        $this->pointsBalance += $points;
        $this->totalEarned += $points;
    }

    public function usePoints(int $points): void
    {
        if ($points > $this->pointsBalance) {
            throw new \DomainException('Insufficient loyalty points.');
        }

        $this->pointsBalance -= $points;
        $this->totalUsed += $points;
    }

    /**
     * Manual correction (Correction transaction): treated like a lifetime
     * earn/use event so dashboard totals stay consistent.
     */
    public function correct(int $delta): void
    {
        $this->pointsBalance += $delta;
        if ($delta > 0) {
            $this->totalEarned += $delta;
        } else {
            $this->totalUsed += abs($delta);
        }
    }

    /**
     * Restitution of previously spent points on cancellation/refund — a
     * reversal, not a new lifetime "earn" event.
     */
    public function restorePoints(int $points): void
    {
        $this->pointsBalance += $points;
    }

    /**
     * Revocation of previously earned points (cancellation/refund) or
     * expiration — reduces the spendable balance only.
     */
    public function revokePoints(int $points): void
    {
        $this->pointsBalance = max(0, $this->pointsBalance - $points);
    }

    /** @return Collection<int, LoyaltyTransaction> */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }
}
