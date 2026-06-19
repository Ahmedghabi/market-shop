<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: 'menu')]
class Menu extends AbstractEntity
{
    public const POSITION_HEADER = 'HEADER';
    public const POSITION_FOOTER = 'FOOTER';
    public const POSITION_OTHER = 'OTHER';

    /** @var Collection<int, MenuItem> */
    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column(length: 20)]
        private string $position = self::POSITION_HEADER,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->items = new ArrayCollection();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
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

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): void
    {
        $this->position = $position;
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

    /** @return Collection<int, MenuItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(MenuItem $item): void
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setMenu($this);
        }
    }

    public function removeItem(MenuItem $item): void
    {
        if ($this->items->removeElement($item)) {
            if ($item->getMenu() === $this) {
                $item->setMenu(null);
            }
        }
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
