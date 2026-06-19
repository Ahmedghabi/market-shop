<?php

namespace App\Entity;

use App\Enum\LoyaltyTransactionType;
use App\Repository\LoyaltyTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoyaltyTransactionRepository::class)]
#[ORM\Table(name: 'loyalty_transaction')]
class LoyaltyTransaction extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: LoyaltyAccount::class, inversedBy: 'transactions')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private LoyaltyAccount $account,
        #[ORM\Column(length: 32, enumType: LoyaltyTransactionType::class)]
        private LoyaltyTransactionType $type,
        #[ORM\Column]
        private int $points,
        #[ORM\ManyToOne(targetEntity: Order::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Order $order = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $reason = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }
}
