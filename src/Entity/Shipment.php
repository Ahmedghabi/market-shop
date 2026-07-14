<?php

namespace App\Entity;

use App\Enum\ShipmentStatus;
use App\Repository\ShipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipmentRepository::class)]
#[ORM\Table(name: 'shipment')]
#[ORM\Index(name: 'idx_shipment_boutique', columns: ['boutique_id'])]
#[ORM\Index(name: 'idx_shipment_status', columns: ['status'])]
class Shipment extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Order::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Order $order,
        #[ORM\ManyToOne(targetEntity: DeliveryCompany::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
        private DeliveryCompany $deliveryCompany,
        #[ORM\ManyToOne(targetEntity: BoutiqueDeliveryAccount::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?BoutiqueDeliveryAccount $credential = null,
        #[ORM\Column(length: 32, enumType: ShipmentStatus::class)]
        private ShipmentStatus $status = ShipmentStatus::Created,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $trackingNumber = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $labelUrl = null,
        #[ORM\Column(nullable: true)]
        private ?int $costCents = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $requestPayload = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $responsePayload = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $errorMessage = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $sentAt = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getDeliveryCompany(): DeliveryCompany
    {
        return $this->deliveryCompany;
    }

    public function getCredential(): ?BoutiqueDeliveryAccount
    {
        return $this->credential;
    }

    public function getStatus(): ShipmentStatus
    {
        return $this->status;
    }

    public function setStatus(ShipmentStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
        $this->touch();
    }

    public function getLabelUrl(): ?string
    {
        return $this->labelUrl;
    }

    public function setLabelUrl(?string $labelUrl): void
    {
        $this->labelUrl = $labelUrl;
        $this->touch();
    }

    public function getCostCents(): ?int
    {
        return $this->costCents;
    }

    public function setCostCents(?int $costCents): void
    {
        $this->costCents = $costCents;
        $this->touch();
    }

    /** @return array<string, mixed>|null */
    public function getRequestPayload(): ?array
    {
        return $this->requestPayload;
    }

    /** @param array<string, mixed>|null $requestPayload */
    public function setRequestPayload(?array $requestPayload): void
    {
        $this->requestPayload = $requestPayload;
        $this->touch();
    }

    /** @return array<string, mixed>|null */
    public function getResponsePayload(): ?array
    {
        return $this->responsePayload;
    }

    /** @param array<string, mixed>|null $responsePayload */
    public function setResponsePayload(?array $responsePayload): void
    {
        $this->responsePayload = $responsePayload;
        $this->touch();
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function markSent(): void
    {
        $this->sentAt = new \DateTimeImmutable();
        $this->touch();
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
