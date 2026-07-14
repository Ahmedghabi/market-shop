<?php

namespace App\Entity;

use App\Enum\SuggestionVisibility;
use App\Repository\SuggestionCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuggestionCommentRepository::class)]
#[ORM\Table(name: 'suggestion_comment')]
#[ORM\Index(name: 'idx_suggestion_comment_suggestion_created', columns: ['suggestion_id', 'created_at'])]
class SuggestionComment extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Suggestion::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Suggestion $suggestion;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Boutique::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Boutique $boutique;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $replies;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(length: 16, enumType: SuggestionVisibility::class)]
    private SuggestionVisibility $visibility;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(Suggestion $suggestion, User $user, Boutique $boutique, string $content, SuggestionVisibility $visibility = SuggestionVisibility::PUBLIC, ?self $parent = null)
    {
        parent::__construct();
        $this->suggestion = $suggestion;
        $this->user = $user;
        $this->boutique = $boutique;
        $this->content = $content;
        $this->visibility = $visibility;
        $this->parent = $parent;
        $this->replies = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getSuggestion(): Suggestion
    {
        return $this->suggestion;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
    }

    /** @return Collection<int, self> */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getVisibility(): SuggestionVisibility
    {
        return $this->visibility;
    }

    public function setVisibility(SuggestionVisibility $visibility): void
    {
        $this->visibility = $visibility;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
