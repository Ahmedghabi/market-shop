<?php

namespace App\Entity;

use App\Enum\ExtensionRequestStatus;
use App\Repository\ExtensionRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtensionRequestRepository::class)]
#[ORM\Table(name: 'extension_request')]
class ExtensionRequest extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Extension::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Extension $extension,
        /**
         * Snapshot of the extension price at request time (prices may change later).
         */
        #[ORM\Column]
        private int $priceTnd = 0,
        #[ORM\Column(length: 32, enumType: ExtensionRequestStatus::class)]
        private ExtensionRequestStatus $status = ExtensionRequestStatus::Draft,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $comment = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $adminComment = null,
        #[ORM\ManyToOne(targetEntity: Invoice::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Invoice $invoice = null,
        #[ORM\Column]
        private \DateTimeImmutable $requestedAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $paidAt = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $decidedAt = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $decidedBy = null,
        #[ORM\ManyToOne(targetEntity: BoutiqueExtension::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?BoutiqueExtension $grant = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getExtension(): Extension
    {
        return $this->extension;
    }

    public function getPriceTnd(): int
    {
        return $this->priceTnd;
    }

    public function getStatus(): ExtensionRequestStatus
    {
        return $this->status;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getAdminComment(): ?string
    {
        return $this->adminComment;
    }

    public function setAdminComment(?string $adminComment): void
    {
        $this->adminComment = $adminComment;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getDecidedAt(): ?\DateTimeImmutable
    {
        return $this->decidedAt;
    }

    public function getDecidedBy(): ?string
    {
        return $this->decidedBy;
    }

    public function getGrant(): ?BoutiqueExtension
    {
        return $this->grant;
    }

    /**
     * Moves the request forward right after creation: free extensions skip payment entirely.
     */
    public function initializeWorkflow(): void
    {
        $this->status = $this->priceTnd > 0
            ? ExtensionRequestStatus::AwaitingPayment
            : ExtensionRequestStatus::AwaitingValidation;
    }

    public function markPaid(): void
    {
        $this->paidAt = new \DateTimeImmutable();
        $this->status = ExtensionRequestStatus::AwaitingValidation;
    }

    public function approve(string $decidedBy, BoutiqueExtension $grant, ?string $adminComment = null): void
    {
        $this->status = ExtensionRequestStatus::Activated;
        $this->decidedAt = new \DateTimeImmutable();
        $this->decidedBy = $decidedBy;
        $this->adminComment = $adminComment ?? $this->adminComment;
        $this->grant = $grant;
    }

    public function reject(string $decidedBy, ?string $adminComment = null): void
    {
        $this->status = ExtensionRequestStatus::Rejected;
        $this->decidedAt = new \DateTimeImmutable();
        $this->decidedBy = $decidedBy;
        $this->adminComment = $adminComment ?? $this->adminComment;
    }

    public function suspend(string $decidedBy, ?string $adminComment = null): void
    {
        $this->status = ExtensionRequestStatus::Suspended;
        $this->decidedAt = new \DateTimeImmutable();
        $this->decidedBy = $decidedBy;
        $this->adminComment = $adminComment ?? $this->adminComment;
    }

    public function cancel(): void
    {
        $this->status = ExtensionRequestStatus::Cancelled;
    }

    public function markExpired(): void
    {
        $this->status = ExtensionRequestStatus::Expired;
    }
}
