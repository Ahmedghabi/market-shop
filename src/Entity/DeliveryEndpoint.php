<?php

namespace App\Entity;

use App\Enum\DeliveryEndpointType;
use App\Enum\DeliveryHttpMethod;
use App\Enum\DeliveryResponseType;
use App\Repository\DeliveryEndpointRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryEndpointRepository::class)]
#[ORM\Table(name: 'delivery_endpoint')]
#[ORM\UniqueConstraint(name: 'uniq_delivery_endpoint_type', columns: ['company_id', 'type'])]
class DeliveryEndpoint extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: DeliveryCompany::class, inversedBy: 'endpoints')]
        #[ORM\JoinColumn(name: 'company_id', nullable: false, onDelete: 'CASCADE')]
        private DeliveryCompany $company,
        #[ORM\Column(length: 32, enumType: DeliveryEndpointType::class)]
        private DeliveryEndpointType $type,
        #[ORM\Column(length: 160)]
        private string $name = '',
        /**
         * Absolute or relative (to the company baseUrl) URL.
         * May contain a {tracking} placeholder for track/label/cancel endpoints.
         */
        #[ORM\Column(length: 500)]
        private string $url = '',
        #[ORM\Column(length: 16, enumType: DeliveryHttpMethod::class)]
        private DeliveryHttpMethod $httpMethod = DeliveryHttpMethod::Post,
        /** @var array<string, string> */
        #[ORM\Column(type: 'json')]
        private array $headers = [],
        #[ORM\Column(length: 16, enumType: DeliveryResponseType::class)]
        private DeliveryResponseType $responseType = DeliveryResponseType::Json,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getCompany(): DeliveryCompany
    {
        return $this->company;
    }

    public function getType(): DeliveryEndpointType
    {
        return $this->type;
    }

    public function setType(DeliveryEndpointType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
        $this->touch();
    }

    public function getHttpMethod(): DeliveryHttpMethod
    {
        return $this->httpMethod;
    }

    public function setHttpMethod(DeliveryHttpMethod $httpMethod): void
    {
        $this->httpMethod = $httpMethod;
        $this->touch();
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /** @param array<string, string> $headers */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
        $this->touch();
    }

    public function getResponseType(): DeliveryResponseType
    {
        return $this->responseType;
    }

    public function setResponseType(DeliveryResponseType $responseType): void
    {
        $this->responseType = $responseType;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
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
