<?php

namespace App\Entity;

use App\Enum\CmsPageStatus;
use App\Enum\CmsPageType;
use App\Repository\CmsPageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CmsPageRepository::class)]
#[ORM\Table(name: 'cms_page')]
#[ORM\UniqueConstraint(name: 'uniq_cms_page_boutique_slug', columns: ['boutique_id', 'slug'])]
#[ORM\Index(name: 'idx_cms_page_boutique_status', columns: ['boutique_id', 'status'])]
class CmsPage extends AbstractEntity
{
    /** @var Collection<int, CmsBlock> */
    #[ORM\OneToMany(mappedBy: 'page', targetEntity: CmsBlock::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $blocks;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 255)]
        private string $title,
        #[ORM\Column(length: 255)]
        private string $slug,
        #[ORM\Column(length: 32, enumType: CmsPageType::class)]
        private CmsPageType $type = CmsPageType::Custom,
        #[ORM\Column(length: 32, enumType: CmsPageStatus::class)]
        private CmsPageStatus $status = CmsPageStatus::Draft,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $description = null,
        #[ORM\Column(type: Types::TEXT, nullable: true)]
        private ?string $content = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $template = null,
        #[ORM\Column]
        private bool $isHomepage = false,
        #[ORM\Column]
        private bool $showInHeader = false,
        #[ORM\Column]
        private bool $showInFooter = false,
        #[ORM\Column]
        private int $sortOrder = 0,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $publishedAt = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $metaTitle = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $metaDescription = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $metaKeywords = null,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $ogTitle = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $ogDescription = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $ogImage = null,
        #[ORM\Column(length: 500, nullable: true)]
        private ?string $canonicalUrl = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
        $this->blocks = new ArrayCollection();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function getType(): CmsPageType
    {
        return $this->type;
    }

    public function setType(CmsPageType $type): void
    {
        $this->type = $type;
        $this->touch();
    }

    public function getStatus(): CmsPageStatus
    {
        return $this->status;
    }

    public function setStatus(CmsPageStatus $status): void
    {
        $this->status = $status;
        if (CmsPageStatus::Published === $status && null === $this->publishedAt) {
            $this->publishedAt = new \DateTimeImmutable();
        }
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
        $this->touch();
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
        $this->touch();
    }

    public function isHomepage(): bool
    {
        return $this->isHomepage;
    }

    public function setIsHomepage(bool $isHomepage): void
    {
        $this->isHomepage = $isHomepage;
        $this->touch();
    }

    public function showInHeader(): bool
    {
        return $this->showInHeader;
    }

    public function setShowInHeader(bool $showInHeader): void
    {
        $this->showInHeader = $showInHeader;
        $this->touch();
    }

    public function showInFooter(): bool
    {
        return $this->showInFooter;
    }

    public function setShowInFooter(bool $showInFooter): void
    {
        $this->showInFooter = $showInFooter;
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

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
        $this->touch();
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
        $this->touch();
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
        $this->touch();
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): void
    {
        $this->ogTitle = $ogTitle;
        $this->touch();
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): void
    {
        $this->ogDescription = $ogDescription;
        $this->touch();
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function setOgImage(?string $ogImage): void
    {
        $this->ogImage = $ogImage;
        $this->touch();
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): void
    {
        $this->canonicalUrl = $canonicalUrl;
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

    /** @return Collection<int, CmsBlock> */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(CmsBlock $block): void
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->setPage($this);
        }
    }

    public function removeBlock(CmsBlock $block): void
    {
        if ($this->blocks->removeElement($block)) {
            if ($block->getPage() === $this) {
                $block->setPage(null);
            }
        }
    }

    public function publish(): void
    {
        $this->status = CmsPageStatus::Published;
        if (null === $this->publishedAt) {
            $this->publishedAt = new \DateTimeImmutable();
        }
        $this->touch();
    }

    public function archive(): void
    {
        $this->status = CmsPageStatus::Archived;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
