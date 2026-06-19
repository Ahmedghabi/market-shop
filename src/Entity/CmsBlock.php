<?php

namespace App\Entity;

use App\Enum\CmsBlockType;
use App\Repository\CmsBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmsBlockRepository::class)]
#[ORM\Table(name: 'cms_block')]
#[ORM\Index(name: 'idx_cms_block_page', columns: ['page_id'])]
class CmsBlock extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: CmsPage::class, inversedBy: 'blocks')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private ?CmsPage $page = null,
        #[ORM\Column(length: 32, enumType: CmsBlockType::class)]
        private CmsBlockType $type = CmsBlockType::Text,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $title = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $content = null,
        #[ORM\Column(type: Types::JSON, nullable: true)]
        private ?array $settings = null,
        #[ORM\Column]
        private int $sortOrder = 0,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getPage(): ?CmsPage
    {
        return $this->page;
    }

    public function setPage(?CmsPage $page): void
    {
        $this->page = $page;
        $this->touch();
    }

    public function getType(): CmsBlockType
    {
        return $this->type;
    }

    public function setType(CmsBlockType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
        $this->touch();
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
        $this->touch();
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): void
    {
        $this->settings = $settings;
        $this->touch();
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
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
