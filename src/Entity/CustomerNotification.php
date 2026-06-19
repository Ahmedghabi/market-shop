<?php

namespace App\Entity;

use App\Repository\CustomerNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerNotificationRepository::class)]
#[ORM\Table(name: 'customer_notification')]
#[ORM\Index(name: 'idx_cust_notif_user_read', columns: ['customer_id', 'is_read'])]
class CustomerNotification extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Customer::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Customer $customer,
        #[ORM\Column(length: 80)]
        private string $type,
        #[ORM\Column(length: 160)]
        private string $title,
        #[ORM\Column(type: 'text')]
        private string $message,
        #[ORM\Column(length: 36, nullable: true)]
        private ?string $relatedOrderId = null,
        #[ORM\Column(length: 36, nullable: true)]
        private ?string $relatedShipmentId = null,
        #[ORM\Column]
        private bool $isRead = false,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $readAt = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRelatedOrderId(): ?string
    {
        return $this->relatedOrderId;
    }

    public function getRelatedShipmentId(): ?string
    {
        return $this->relatedShipmentId;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function markRead(): void
    {
        $this->isRead = true;
        $this->readAt = new \DateTimeImmutable();
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
