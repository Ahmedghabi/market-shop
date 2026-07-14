<?php

namespace App\Entity;

use App\Enum\SuggestionReactionType;
use App\Repository\SuggestionReactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuggestionReactionRepository::class)]
#[ORM\Table(name: 'suggestion_reaction')]
#[ORM\UniqueConstraint(name: 'uniq_suggestion_reaction_user', columns: ['suggestion_id', 'user_id'])]
#[ORM\Index(name: 'idx_suggestion_reaction_suggestion_type', columns: ['suggestion_id', 'type'])]
class SuggestionReaction extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Suggestion::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Suggestion $suggestion;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Boutique::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Boutique $boutique;

    #[ORM\Column(length: 32, enumType: SuggestionReactionType::class)]
    private SuggestionReactionType $type;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct(Suggestion $suggestion, User $user, Boutique $boutique, SuggestionReactionType $type)
    {
        parent::__construct();
        $this->suggestion = $suggestion;
        $this->user = $user;
        $this->boutique = $boutique;
        $this->type = $type;
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

    public function getType(): SuggestionReactionType
    {
        return $this->type;
    }

    public function setType(SuggestionReactionType $type): void
    {
        $this->type = $type;
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
