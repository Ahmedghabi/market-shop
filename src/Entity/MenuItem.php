<?php

namespace App\Entity;

use App\Repository\MenuItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
#[ORM\Table(name: 'menu_item')]
class MenuItem extends AbstractEntity
{
    public const TYPE_HOME = 'HOME';
    public const TYPE_PAGE = 'PAGE';
    public const TYPE_CATEGORY = 'CATEGORY';
    public const TYPE_PRODUCT = 'PRODUCT';
    public const TYPE_URL = 'URL';
    public const TYPE_CONTACT = 'CONTACT';

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'items')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ?Menu $menu = null,
        #[ORM\Column(length: 255)]
        private string $title,
        #[ORM\Column(length: 30)]
        private string $type = self::TYPE_URL,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $target = null,
        #[ORM\ManyToOne(targetEntity: self::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?self $parent = null,
        #[ORM\Column]
        private int $position = 0,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): void
    {
        $this->menu = $menu;
        $this->touch();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->touch();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): void
    {
        $this->target = $target;
        $this->touch();
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
        $this->touch();
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
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

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
