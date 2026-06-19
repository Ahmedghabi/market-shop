<?php

namespace App\Entity;

use App\Repository\LoyaltyAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoyaltyAccountRepository::class)]
#[ORM\Table(name: 'loyalty_account')]
#[ORM\UniqueConstraint(name: 'uniq_loyalty_boutique_customer', columns: ['boutique_id', 'customer_id'])]
class LoyaltyAccount extends AbstractEntity
{
    /** @var Collection<int, LoyaltyTransaction> */
    #[ORM\OneToMany(mappedBy: 'account', targetEntity: LoyaltyTransaction::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $transactions;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\OneToOne(targetEntity: Customer::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Customer $customer,
        #[ORM\Column]
        private int $pointsBalance = 0,
    ) {
        parent::__construct();
        $this->transactions = new ArrayCollection();
    }
}
