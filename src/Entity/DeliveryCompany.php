<?php

namespace App\Entity;

use App\Enum\DeliveryAuthType;
use App\Repository\DeliveryCompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryCompanyRepository::class)]
#[ORM\Table(name: 'delivery_company')]
class DeliveryCompany extends AbstractEntity
{
    /** @var Collection<int, DeliveryEndpoint> */
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: DeliveryEndpoint::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['type' => 'ASC'])]
    private Collection $endpoints;

    public function __construct(
        #[ORM\Column(length: 120)]
        private string $name = '',
        #[ORM\Column(length: 120, unique: true)]
        private string $slug = '',
        #[ORM\Column(length: 255)]
        private string $baseUrl = '',
        /**
         * Connector code used to resolve the DeliveryProviderInterface implementation.
         * Falls back to the "generic_http" data-driven connector when no dedicated
         * connector matches this code.
         */
        #[ORM\Column(length: 64)]
        private string $provider = 'generic_http',
        #[ORM\Column(length: 32, enumType: DeliveryAuthType::class)]
        private DeliveryAuthType $authType = DeliveryAuthType::Basic,
        /**
         * Extra auth settings: header names, api key location, custom signature params...
         *
         * @var array<string, mixed>
         */
        #[ORM\Column(type: 'json')]
        private array $authConfig = [],
        /**
         * Dynamic mapping definition: apiField => "{{variable}}" template.
         *
         * @var array<string, mixed>
         */
        #[ORM\Column(type: 'json')]
        private array $mappingConfig = [],
        /**
         * Free-form provider parameters (timeouts, default headers, misc options).
         *
         * @var array<string, mixed>
         */
        #[ORM\Column(type: 'json')]
        private array $parametersConfig = [],
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $logoUrl = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->endpoints = new ArrayCollection();
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
        $this->touch();
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
        $this->touch();
    }

    public function getAuthType(): DeliveryAuthType
    {
        return $this->authType;
    }

    public function setAuthType(DeliveryAuthType $authType): void
    {
        $this->authType = $authType;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getAuthConfig(): array
    {
        return $this->authConfig;
    }

    /** @param array<string, mixed> $authConfig */
    public function setAuthConfig(array $authConfig): void
    {
        $this->authConfig = $authConfig;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getMappingConfig(): array
    {
        return $this->mappingConfig;
    }

    /** @param array<string, mixed> $mappingConfig */
    public function setMappingConfig(array $mappingConfig): void
    {
        $this->mappingConfig = $mappingConfig;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getParametersConfig(): array
    {
        return $this->parametersConfig;
    }

    /** @param array<string, mixed> $parametersConfig */
    public function setParametersConfig(array $parametersConfig): void
    {
        $this->parametersConfig = $parametersConfig;
        $this->touch();
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
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

    /** @return Collection<int, DeliveryEndpoint> */
    public function getEndpoints(): Collection
    {
        return $this->endpoints;
    }

    public function getEndpoint(\App\Enum\DeliveryEndpointType $type): ?DeliveryEndpoint
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->getType() === $type && $endpoint->isActive()) {
                return $endpoint;
            }
        }

        return null;
    }

    public function addEndpoint(DeliveryEndpoint $endpoint): void
    {
        if (!$this->endpoints->contains($endpoint)) {
            $this->endpoints->add($endpoint);
        }
    }

    public function removeEndpoint(DeliveryEndpoint $endpoint): void
    {
        $this->endpoints->removeElement($endpoint);
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
