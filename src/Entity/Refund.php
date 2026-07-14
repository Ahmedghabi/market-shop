<?php

namespace App\Entity;

use App\Enum\RefundStatus;
use App\Enum\RefundType;
use App\Repository\RefundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefundRepository::class)]
#[ORM\Table(name: 'refund')]
#[ORM\UniqueConstraint(name: 'uniq_refund_number', columns: ['refund_number'])]
class Refund extends AbstractEntity
{
    /** @var Collection<int, RefundItem> */
    #[ORM\OneToMany(mappedBy: 'refund', targetEntity: RefundItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(
        #[ORM\Column(length: 32, unique: true)]
        private string $refundNumber,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Order::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Order $order,
        #[ORM\ManyToOne(targetEntity: Customer::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Customer $customer,
        #[ORM\Column(length: 32, enumType: RefundType::class)]
        private RefundType $type,
        #[ORM\Column(length: 32, enumType: RefundStatus::class)]
        private RefundStatus $status = RefundStatus::Pending,
        #[ORM\Column(length: 3)]
        private string $currency = 'TND',
        #[ORM\Column]
        private int $subtotalCents = 0,
        #[ORM\Column]
        private int $taxCents = 0,
        #[ORM\Column]
        private int $totalCents = 0,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $reason = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $processedBy = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $processedAt = null,
        #[ORM\ManyToOne(targetEntity: Invoice::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Invoice $creditNote = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->items = new ArrayCollection();
    }

    public function getRefundNumber(): string
    {
        return $this->refundNumber;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getType(): RefundType
    {
        return $this->type;
    }

    public function getStatus(): RefundStatus
    {
        return $this->status;
    }

    public function setStatus(RefundStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSubtotalCents(): int
    {
        return $this->subtotalCents;
    }

    public function getTaxCents(): int
    {
        return $this->taxCents;
    }

    public function getTotalCents(): int
    {
        return $this->totalCents;
    }

    public function setTotals(int $subtotalCents, int $taxCents, int $totalCents): void
    {
        $this->subtotalCents = $subtotalCents;
        $this->taxCents = $taxCents;
        $this->totalCents = $totalCents;
        $this->touch();
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
        $this->touch();
    }

    public function getProcessedBy(): ?string
    {
        return $this->processedBy;
    }

    public function getProcessedAt(): ?\DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function markProcessed(?string $processedBy = null): void
    {
        $this->status = RefundStatus::Processed;
        $this->processedBy = $processedBy;
        $this->processedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function approve(?string $processedBy = null): void
    {
        $this->status = RefundStatus::Approved;
        $this->processedBy = $processedBy;
        $this->processedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function reject(?string $processedBy = null): void
    {
        $this->status = RefundStatus::Rejected;
        $this->processedBy = $processedBy;
        $this->processedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function getCreditNote(): ?Invoice
    {
        return $this->creditNote;
    }

    public function setCreditNote(?Invoice $creditNote): void
    {
        $this->creditNote = $creditNote;
        $this->touch();
    }

    /** @return Collection<int, RefundItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(RefundItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $this->touch();
        }
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
