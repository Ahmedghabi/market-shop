<?php

namespace App\Entity;

use App\Enum\InvoiceStatus;
use App\Enum\InvoiceType;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table(name: 'invoice')]
#[ORM\UniqueConstraint(name: 'uniq_invoice_number', columns: ['invoice_number'])]
class Invoice extends AbstractEntity
{
    /** @var Collection<int, InvoiceItem> */
    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct(
        #[ORM\Column(length: 32, unique: true)]
        private string $invoiceNumber,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Customer::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Customer $customer,
        #[ORM\ManyToOne(targetEntity: Order::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Order $order,
        #[ORM\ManyToOne(targetEntity: Subscription::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Subscription $subscription,
        #[ORM\Column(length: 32, enumType: InvoiceType::class)]
        private InvoiceType $type,
        #[ORM\Column(length: 32, enumType: InvoiceStatus::class)]
        private InvoiceStatus $status,
        #[ORM\Column(length: 3)]
        private string $currency,
        #[ORM\Column]
        private int $subtotal = 0,
        #[ORM\Column]
        private int $discountTotal = 0,
        #[ORM\Column]
        private int $taxTotal = 0,
        #[ORM\Column]
        private int $shippingTotal = 0,
        #[ORM\Column]
        private int $total = 0,
        #[ORM\Column]
        private \DateTimeImmutable $issuedAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $dueDate = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $paidAt = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $pdfPath = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $boutiqueName = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $boutiqueEmail = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $boutiquePhone = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $boutiqueAddress = null,
        #[ORM\Column(length: 240, nullable: true)]
        private ?string $customerName = null,
        #[ORM\Column(length: 180, nullable: true)]
        private ?string $customerEmail = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $customerPhone = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $customerAddress = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $customerCity = null,
        #[ORM\Column(length: 32, nullable: true)]
        private ?string $customerPostalCode = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $customerCountry = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->items = new ArrayCollection();
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function getType(): InvoiceType
    {
        return $this->type;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function setStatus(InvoiceStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    public function getDiscountTotal(): int
    {
        return $this->discountTotal;
    }

    public function getTaxTotal(): int
    {
        return $this->taxTotal;
    }

    public function getShippingTotal(): int
    {
        return $this->shippingTotal;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotals(int $subtotal, int $discountTotal, int $taxTotal, int $shippingTotal, int $total): void
    {
        $this->subtotal = $subtotal;
        $this->discountTotal = $discountTotal;
        $this->taxTotal = $taxTotal;
        $this->shippingTotal = $shippingTotal;
        $this->total = $total;
        $this->touch();
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): void
    {
        $this->dueDate = $dueDate;
        $this->touch();
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function markPaid(?\DateTimeImmutable $paidAt = null): void
    {
        $this->paidAt = $paidAt ?? new \DateTimeImmutable();
        $this->status = InvoiceStatus::Paid;
        $this->touch();
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): void
    {
        $this->pdfPath = $pdfPath;
        $this->touch();
    }

    public function setBoutiqueSnapshot(?string $name, ?string $email, ?string $phone, ?string $address): void
    {
        $this->boutiqueName = $name;
        $this->boutiqueEmail = $email;
        $this->boutiquePhone = $phone;
        $this->boutiqueAddress = $address;
        $this->touch();
    }

    public function setCustomerSnapshot(?string $name, ?string $email, ?string $phone, ?string $address, ?string $city, ?string $postalCode, ?string $country): void
    {
        $this->customerName = $name;
        $this->customerEmail = $email;
        $this->customerPhone = $phone;
        $this->customerAddress = $address;
        $this->customerCity = $city;
        $this->customerPostalCode = $postalCode;
        $this->customerCountry = $country;
        $this->touch();
    }

    public function getBoutiqueName(): ?string
    {
        return $this->boutiqueName;
    }

    public function getBoutiqueEmail(): ?string
    {
        return $this->boutiqueEmail;
    }

    public function getBoutiquePhone(): ?string
    {
        return $this->boutiquePhone;
    }

    public function getBoutiqueAddress(): ?string
    {
        return $this->boutiqueAddress;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function getCustomerPhone(): ?string
    {
        return $this->customerPhone;
    }

    public function getCustomerAddress(): ?string
    {
        return $this->customerAddress;
    }

    public function getCustomerCity(): ?string
    {
        return $this->customerCity;
    }

    public function getCustomerPostalCode(): ?string
    {
        return $this->customerPostalCode;
    }

    public function getCustomerCountry(): ?string
    {
        return $this->customerCountry;
    }

    /** @return Collection<int, InvoiceItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $item): void
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
