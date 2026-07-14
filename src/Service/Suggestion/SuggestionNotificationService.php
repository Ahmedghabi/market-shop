<?php

namespace App\Service\Suggestion;

use App\Entity\Suggestion;
use App\Entity\SuggestionComment;
use App\Entity\SuggestionReaction;
use App\Repository\UserRepository;
use App\Service\NotificationService;

final readonly class SuggestionNotificationService
{
    public function __construct(private NotificationService $notifications, private UserRepository $users)
    {
    }

    public function submitted(Suggestion $suggestion): void
    {
        foreach ($this->users->findByRole('ROLE_BOUTIQUE_ADMIN') as $admin) {
            if ($admin->getAdministeredBoutiques()->contains($suggestion->getBoutique())) {
                $this->notifications->notify($admin->getUserIdentifier(), 'suggestion_submitted', 'New suggestion', $suggestion->getTitle(), $suggestion->getBoutique());
            }
        }
    }

    public function statusChanged(Suggestion $suggestion): void
    {
        $this->notifications->notify($suggestion->getCreatedBy()->getUserIdentifier(), 'suggestion_status_changed', 'Suggestion status updated', sprintf('%s is now %s.', $suggestion->getTitle(), $suggestion->getStatus()->value), $suggestion->getBoutique());
    }

    public function officialResponse(Suggestion $suggestion): void
    {
        $this->notifications->notify($suggestion->getCreatedBy()->getUserIdentifier(), 'suggestion_official_response', 'Official response added', sprintf('An official response was added to "%s".', $suggestion->getTitle()), $suggestion->getBoutique());
    }

    public function comment(SuggestionComment $comment): void
    {
        $suggestion = $comment->getSuggestion();
        if ($suggestion->getCreatedBy() !== $comment->getUser()) {
            $this->notifications->notify($suggestion->getCreatedBy()->getUserIdentifier(), 'suggestion_comment', 'New suggestion comment', $suggestion->getTitle(), $suggestion->getBoutique());
        }
    }

    public function reaction(SuggestionReaction $reaction): void
    {
        $suggestion = $reaction->getSuggestion();
        if ($suggestion->getCreatedBy() !== $reaction->getUser()) {
            $this->notifications->notify($suggestion->getCreatedBy()->getUserIdentifier(), 'suggestion_reaction', 'New suggestion reaction', $suggestion->getTitle(), $suggestion->getBoutique());
        }
    }
}
