<?php

namespace App\Entity;

use App\Enum\DeliveryEndpointType;
use App\Repository\DeliveryApiLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Secured audit trail of every outbound call made to a delivery connector.
 * Request/response bodies must be redacted (secrets stripped) before storage.
 */
#[ORM\Entity(repositoryClass: DeliveryApiLogRepository::class)]
#[ORM\Table(name: 'delivery_api_log')]
#[ORM\Index(name: 'idx_delivery_log_company', columns: ['delivery_company_id'])]
#[ORM\Index(name: 'idx_delivery_log_boutique', columns: ['boutique_id'])]
class DeliveryApiLog extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: DeliveryCompany::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private DeliveryCompany $deliveryCompany,
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?Boutique $boutique = null,
        #[ORM\Column(length: 32, nullable: true, enumType: DeliveryEndpointType::class)]
        private ?DeliveryEndpointType $endpointType = null,
        #[ORM\Column(length: 8)]
        private string $requestMethod = 'POST',
        #[ORM\Column(length: 500)]
        private string $requestUrl = '',
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $requestBody = null,
        #[ORM\Column(nullable: true)]
        private ?int $responseStatus = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $responseBody = null,
        #[ORM\Column]
        private bool $success = false,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $errorMessage = null,
        #[ORM\Column(nullable: true)]
        private ?int $durationMs = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        parent::__construct();
    }

    public function getDeliveryCompany(): DeliveryCompany
    {
        return $this->deliveryCompany;
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function getEndpointType(): ?DeliveryEndpointType
    {
        return $this->endpointType;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getRequestUrl(): string
    {
        return $this->requestUrl;
    }

    /** @return array<string, mixed>|null */
    public function getRequestBody(): ?array
    {
        return $this->requestBody;
    }

    public function getResponseStatus(): ?int
    {
        return $this->responseStatus;
    }

    /** @return array<string, mixed>|null */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getDurationMs(): ?int
    {
        return $this->durationMs;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
