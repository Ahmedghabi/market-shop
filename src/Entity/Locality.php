<?php

namespace App\Entity;

use App\Repository\LocalityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocalityRepository::class)]
#[ORM\Table(name: 'locality')]
#[ORM\Index(columns: ['name'], name: 'idx_locality_name')]
#[ORM\Index(columns: ['postal_code'], name: 'idx_locality_postal_code')]
class Locality extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Governorate::class, inversedBy: 'localities')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Governorate $governorate;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        Governorate $governorate,
        string $name,
        ?string $postalCode = null,
    ) {
        parent::__construct();
        $this->governorate = $governorate;
        $this->name = $name;
        $this->postalCode = $postalCode;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getGovernorate(): Governorate
    {
        return $this->governorate;
    }

    public function setGovernorate(Governorate $governorate): void
    {
        $this->governorate = $governorate;
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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
        $this->touch();
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): void
    {
        $this->latitude = $latitude;
        $this->touch();
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): void
    {
        $this->longitude = $longitude;
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
