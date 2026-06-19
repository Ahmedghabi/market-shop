<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'country')]
class Country extends AbstractEntity
{
    #[ORM\Column(length: 64, unique: true)]
    private string $name;

    #[ORM\Column(length: 4, unique: true)]
    private string $code;

    #[ORM\Column(length: 3, nullable: true)]
    private ?string $phoneCode = null;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Governorate> */
    #[ORM\OneToMany(targetEntity: Governorate::class, mappedBy: 'country', cascade: ['persist'])]
    private Collection $governorates;

    public function __construct(
        string $name,
        string $code,
        ?string $phoneCode = null,
    ) {
        parent::__construct();
        $this->name = $name;
        $this->code = $code;
        $this->phoneCode = $phoneCode;
        $this->createdAt = new \DateTimeImmutable();
        $this->governorates = new ArrayCollection();
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->touch();
    }

    public function getPhoneCode(): ?string
    {
        return $this->phoneCode;
    }

    public function setPhoneCode(?string $phoneCode): void
    {
        $this->phoneCode = $phoneCode;
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

    /** @return Collection<int, Governorate> */
    public function getGovernorates(): Collection
    {
        return $this->governorates;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
