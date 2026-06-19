<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Repository\BoutiqueSponsorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoutiqueSponsorRepository::class)]
#[ORM\Table(name: 'boutique_sponsor')]
#[ORM\UniqueConstraint(name: 'uniq_boutique_sponsor', columns: ['boutique_id', 'sponsor_id'])]
class BoutiqueSponsor extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: Sponsor::class, inversedBy: 'boutiques')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Sponsor $sponsor,
        #[ORM\Column]
        private int $position = 0,
        #[ORM\Column]
        private bool $active = true,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getSponsor(): Sponsor
    {
        return $this->sponsor;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
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
