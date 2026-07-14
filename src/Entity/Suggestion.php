<?php

namespace App\Entity;

use App\Enum\SuggestionStatus;
use App\Enum\SuggestionVisibility;
use App\Repository\SuggestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SuggestionRepository::class)]
#[ORM\Table(name: 'suggestion')]
#[ORM\Index(name: 'idx_suggestion_tenant_status_created', columns: ['tenant_id', 'status', 'created_at'])]
#[ORM\Index(name: 'idx_suggestion_public', columns: ['is_published', 'visibility', 'published_at'])]
#[ORM\Index(name: 'idx_suggestion_category', columns: ['category_id'])]
class Suggestion extends AbstractEntity
{
    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\ManyToOne(targetEntity: SuggestionCategory::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?SuggestionCategory $category = null;

    #[ORM\ManyToOne(targetEntity: Boutique::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Boutique $boutique;

    #[ORM\Column(type: 'uuid')]
    private Uuid $tenantId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private User $createdBy;

    #[ORM\Column(length: 32, enumType: SuggestionStatus::class)]
    private SuggestionStatus $status = SuggestionStatus::DRAFT;

    #[ORM\Column(length: 16, enumType: SuggestionVisibility::class)]
    private SuggestionVisibility $visibility = SuggestionVisibility::PrivateVisibility;

    #[ORM\Column]
    private bool $isPublished = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $officialResponse = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $officialResponseBy = null;

    #[ORM\Column]
    private bool $showAuthorPublic = false;

    #[ORM\Column]
    private bool $showBoutiquePublic = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    /** @var Collection<int, SuggestionReaction> */
    #[ORM\OneToMany(mappedBy: 'suggestion', targetEntity: SuggestionReaction::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reactions;

    /** @var Collection<int, SuggestionComment> */
    #[ORM\OneToMany(mappedBy: 'suggestion', targetEntity: SuggestionComment::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $comments;

    /** @var Collection<int, SuggestionStatusHistory> */
    #[ORM\OneToMany(mappedBy: 'suggestion', targetEntity: SuggestionStatusHistory::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $statusHistory;

    public function __construct(Boutique $boutique, User $createdBy, string $title, string $description)
    {
        parent::__construct();
        $this->boutique = $boutique;
        $this->tenantId = $boutique->getId();
        $this->createdBy = $createdBy;
        $this->title = $title;
        $this->description = $description;
        $this->createdAt = new \DateTimeImmutable();
        $this->reactions = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->statusHistory = new ArrayCollection();
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function getCategory(): ?SuggestionCategory
    {
        return $this->category;
    }

    public function setCategory(?SuggestionCategory $category): void
    {
        $this->category = $category;
        $this->touch();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getTenantId(): Uuid
    {
        return $this->tenantId;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getStatus(): SuggestionStatus
    {
        return $this->status;
    }

    public function setStatus(SuggestionStatus $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function getVisibility(): SuggestionVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(SuggestionVisibility $visibility): void
    {
        $this->visibility = $visibility;
        $this->touch();
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function publish(): void
    {
        $this->isPublished = true;
        $this->publishedAt ??= new \DateTimeImmutable();
        $this->touch();
    }

    public function unpublish(): void
    {
        $this->isPublished = false;
        $this->publishedAt = null;
        $this->touch();
    }

    public function getOfficialResponse(): ?string
    {
        return $this->officialResponse;
    }

    public function setOfficialResponse(?string $response): void
    {
        $this->officialResponse = $response;
        $this->touch();
    }

    public function getOfficialResponseBy(): ?User
    {
        return $this->officialResponseBy;
    }

    public function setOfficialResponseBy(?User $user): void
    {
        $this->officialResponseBy = $user;
        $this->touch();
    }

    public function isShowAuthorPublic(): bool
    {
        return $this->showAuthorPublic;
    }

    public function setShowAuthorPublic(bool $show): void
    {
        $this->showAuthorPublic = $show;
        $this->touch();
    }

    public function isShowBoutiquePublic(): bool
    {
        return $this->showBoutiquePublic;
    }

    public function setShowBoutiquePublic(bool $show): void
    {
        $this->showBoutiquePublic = $show;
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

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function markClosed(): void
    {
        $this->closedAt = new \DateTimeImmutable();
        $this->touch();
    }

    public function close(): void
    {
        $this->closedAt = new \DateTimeImmutable();
        $this->status = SuggestionStatus::ARCHIVED;
        $this->isPublished = false;
        $this->touch();
    }

    /** @return Collection<int, SuggestionReaction> */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    /** @return Collection<int, SuggestionComment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /** @return Collection<int, SuggestionStatusHistory> */
    public function getStatusHistory(): Collection
    {
        return $this->statusHistory;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
