<?php

namespace App\Service\Suggestion;

use App\Dto\Suggestion\SuggestionCategoryOutput;
use App\Dto\Suggestion\SuggestionCommentOutput;
use App\Dto\Suggestion\SuggestionHistoryOutput;
use App\Dto\Suggestion\SuggestionOutput;
use App\Dto\Suggestion\SuggestionReactionOutput;
use App\Entity\Suggestion;
use App\Entity\SuggestionCategory;
use App\Entity\SuggestionComment;
use App\Entity\SuggestionReaction;
use App\Entity\SuggestionStatusHistory;
use App\Entity\User;
use App\Repository\SuggestionCommentRepository;
use App\Repository\SuggestionReactionRepository;
use App\Repository\SuggestionStatusHistoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class SuggestionOutputMapper
{
    public function __construct(
        private SuggestionReactionRepository $reactions,
        private SuggestionCommentRepository $comments,
        private SuggestionStatusHistoryRepository $history,
        private Security $security,
        private UserRepository $users,
    ) {
    }

    public function suggestion(Suggestion $entity, bool $public = false, bool $details = false): SuggestionOutput
    {
        $output = new SuggestionOutput();
        $output->id = (string) $entity->getId();
        $output->title = $entity->getTitle();
        $output->description = $entity->getDescription();
        $output->categoryId = $entity->getCategory() ? (string) $entity->getCategory()->getId() : null;
        $output->categoryName = $entity->getCategory()?->getName();
        $output->status = $entity->getStatus()->value;
        $output->visibility = $entity->getVisibility()->value;
        $output->isPublished = $entity->isPublished();
        $output->showAuthorPublic = $entity->isShowAuthorPublic();
        $output->showBoutiquePublic = $entity->isShowBoutiquePublic();
        $output->officialResponse = $entity->getOfficialResponse();
        $output->officialResponseBy = $public ? null : ($entity->getOfficialResponseBy()?->getDisplayName() ?? $entity->getOfficialResponseBy()?->getUserIdentifier());
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');
        $output->publishedAt = $entity->getPublishedAt()?->format('c');
        $output->closedAt = $entity->getClosedAt()?->format('c');

        if (!$public || $entity->isShowBoutiquePublic()) {
            $output->boutiqueId = (string) $entity->getBoutique()->getId();
            $output->boutiqueName = $entity->getBoutique()->getName();
        }
        if (!$public || $entity->isShowAuthorPublic()) {
            $output->authorId = (string) $entity->getCreatedBy()->getId();
            $output->authorName = $entity->getCreatedBy()->getDisplayName() ?? $entity->getCreatedBy()->getUserIdentifier();
        }

        $output->reactionCounts = $this->reactions->countByType($entity);
        $output->reactionCount = array_sum($output->reactionCounts);
        $output->commentCount = $this->comments->countBySuggestion($entity);
        $authenticated = $this->security->getUser();
        $user = $authenticated instanceof User
            ? $authenticated
            : (null !== $authenticated ? $this->users->findOneBy(['identifier' => $authenticated->getUserIdentifier()]) : null);
        if ($user instanceof User) {
            $output->currentUserReaction = $this->reactions->findOneBySuggestionAndUser($entity, $user)?->getType()->value;
        }

        if ($details) {
            $output->history = $public ? [] : array_map(fn (SuggestionStatusHistory $item) => $this->history($item), $this->history->findBySuggestion($entity));
            $comments = $this->comments->findBySuggestion($entity);
            if ($public) {
                $comments = array_values(array_filter($comments, static fn (SuggestionComment $item): bool => 'public' === $item->getVisibility()->value));
            }
            $output->comments = array_map(fn (SuggestionComment $item) => $this->comment($item, $public), $comments);
        }

        return $output;
    }

    public function reaction(SuggestionReaction $entity): SuggestionReactionOutput
    {
        $output = new SuggestionReactionOutput();
        $output->id = (string) $entity->getId();
        $output->suggestionId = (string) $entity->getSuggestion()->getId();
        $output->userId = (string) $entity->getUser()->getId();
        $output->type = $entity->getType()->value;
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }

    public function comment(SuggestionComment $entity, bool $public = false): SuggestionCommentOutput
    {
        $output = new SuggestionCommentOutput();
        $output->id = (string) $entity->getId();
        $output->suggestionId = (string) $entity->getSuggestion()->getId();
        $output->parentId = $entity->getParent() ? (string) $entity->getParent()->getId() : null;
        $output->content = $entity->getContent();
        $output->visibility = $entity->getVisibility()->value;
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');
        if (!$public || $entity->getSuggestion()->isShowAuthorPublic()) {
            $output->userId = (string) $entity->getUser()->getId();
            $output->authorName = $entity->getUser()->getDisplayName() ?? $entity->getUser()->getUserIdentifier();
        }

        return $output;
    }

    public function history(SuggestionStatusHistory $entity): SuggestionHistoryOutput
    {
        $output = new SuggestionHistoryOutput();
        $output->id = (string) $entity->getId();
        $output->oldStatus = $entity->getOldStatus()?->value;
        $output->newStatus = $entity->getNewStatus()->value;
        $output->changedBy = $entity->getChangedBy()?->getDisplayName() ?? $entity->getChangedBy()?->getUserIdentifier();
        $output->comment = $entity->getComment();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }

    public function category(SuggestionCategory $entity): SuggestionCategoryOutput
    {
        $output = new SuggestionCategoryOutput();
        $output->id = (string) $entity->getId();
        $output->name = $entity->getName();
        $output->slug = $entity->getSlug();
        $output->description = $entity->getDescription();
        $output->isActive = $entity->isActive();
        $output->position = $entity->getPosition();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
