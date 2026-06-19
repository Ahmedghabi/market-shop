<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Enum\SponsorScope;
use App\Repository\SponsorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
#[ORM\Table(name: 'sponsor')]
class Sponsor extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, BoutiqueSponsor> */
    #[ORM\OneToMany(mappedBy: 'sponsor', targetEntity: BoutiqueSponsor::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $boutiques;

    public function __construct(
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 32, enumType: SponsorScope::class)]
        private SponsorScope $scope = SponsorScope::Global,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $logoUrl = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $targetUrl = null,
        #[ORM\Column]
        private bool $active = true,
    ) {
        parent::__construct();
        $this->boutiques = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getScope(): SponsorScope
    {
        return $this->scope;
    }

    public function setScope(SponsorScope $scope): void
    {
        $this->scope = $scope;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    public function getTargetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function setTargetUrl(?string $targetUrl): void
    {
        $this->targetUrl = $targetUrl;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
