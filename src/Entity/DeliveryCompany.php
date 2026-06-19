<?php

namespace App\Entity;

use App\Repository\DeliveryCompanyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryCompanyRepository::class)]
#[ORM\Table(name: 'delivery_company')]
class DeliveryCompany extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 120)]
        private string $name = '',
        #[ORM\Column(length: 120, unique: true)]
        private string $slug = '',
        #[ORM\Column(length: 255)]
        private string $baseUrl = '',
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $authEndpoint = null,
        #[ORM\Column(length: 255)]
        private string $submitOrderEndpoint = '/orders',
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $trackEndpoint = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column]
        private bool $isActive = true,
    ) {
        parent::__construct();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getAuthEndpoint(): ?string
    {
        return $this->authEndpoint;
    }

    public function setAuthEndpoint(?string $authEndpoint): void
    {
        $this->authEndpoint = $authEndpoint;
    }

    public function getSubmitOrderEndpoint(): string
    {
        return $this->submitOrderEndpoint;
    }

    public function setSubmitOrderEndpoint(string $submitOrderEndpoint): void
    {
        $this->submitOrderEndpoint = $submitOrderEndpoint;
    }

    public function getTrackEndpoint(): ?string
    {
        return $this->trackEndpoint;
    }

    public function setTrackEndpoint(?string $trackEndpoint): void
    {
        $this->trackEndpoint = $trackEndpoint;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
