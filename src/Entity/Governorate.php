<?php

namespace App\Entity;

use App\Repository\GovernorateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GovernorateRepository::class)]
#[ORM\Table(name: 'governorate')]
class Governorate extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Country::class, inversedBy: 'governorates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Country $country;

    #[ORM\Column(length: 64, unique: true)]
    private string $name;

    #[ORM\Column(length: 5, unique: true)]
    private string $code;

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /** @var Collection<int, Locality> */
    #[ORM\OneToMany(targetEntity: Locality::class, mappedBy: 'governorate', cascade: ['persist', 'remove'])]
    private Collection $localities;

    public function __construct(
        Country $country,
        string $name,
        string $code,
    ) {
        parent::__construct();
        $this->country = $country;
        $this->name = $name;
        $this->code = $code;
        $this->createdAt = new \DateTimeImmutable();
        $this->localities = new ArrayCollection();
    }

    public function getCountry(): Country
    {
        return $this->country;
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

    /** @return Collection<int, Locality> */
    public function getLocalities(): Collection
    {
        return $this->localities;
    }

    public function addLocality(Locality $locality): void
    {
        if (!$this->localities->contains($locality)) {
            $this->localities->add($locality);
            $locality->setGovernorate($this);
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
