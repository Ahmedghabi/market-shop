<?php

namespace App\Entity;

use App\Repository\CustomerLoyaltyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerLoyaltyRepository::class)]
#[ORM\Table(name: 'customer_loyalty')]
#[ORM\UniqueConstraint(name: 'uniq_customer_loyalty', columns: ['customer_id', 'boutique_id'])]
class CustomerLoyalty extends AbstractEntity
{
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
}
