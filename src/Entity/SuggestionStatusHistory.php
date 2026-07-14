<?php

namespace App\Entity;

use App\Enum\SuggestionStatus;
use App\Repository\SuggestionStatusHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuggestionStatusHistoryRepository::class)]
#[ORM\Table(name: 'suggestion_status_history')]
#[ORM\Index(name: 'idx_suggestion_history_suggestion_created', columns: ['suggestion_id', 'created_at'])]
class SuggestionStatusHistory extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Suggestion::class, inversedBy: 'statusHistory')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Suggestion $suggestion;

    #[ORM\Column(length: 32, nullable: true, enumType: SuggestionStatus::class)]
    private ?SuggestionStatus $oldStatus;

    #[ORM\Column(length: 32, enumType: SuggestionStatus::class)]
    private SuggestionStatus $newStatus;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $changedBy;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Suggestion $suggestion, ?SuggestionStatus $oldStatus, SuggestionStatus $newStatus, ?User $changedBy = null, ?string $comment = null)
    {
        parent::__construct();
        $this->suggestion = $suggestion;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->comment = $comment;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getSuggestion(): Suggestion
    {
        return $this->suggestion;
    }

    public function getOldStatus(): ?SuggestionStatus
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): SuggestionStatus
    {
        return $this->newStatus;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
