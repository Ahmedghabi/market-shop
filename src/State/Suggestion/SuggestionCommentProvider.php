<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Suggestion;
use App\Entity\SuggestionComment;
use App\Repository\SuggestionCommentRepository;
use App\Repository\SuggestionRepository;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionOutputMapper;

final readonly class SuggestionCommentProvider implements ProviderInterface
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionCommentRepository $comments, private SuggestionAccessService $access, private SuggestionOutputMapper $mapper)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $suggestion = $this->suggestions->find($uriVariables['suggestionId'] ?? '');
        if (!$suggestion instanceof Suggestion) {
            return null;
        }
        $this->access->assertCanRead($suggestion);
        $user = $this->access->requireUser();
        $admin = $this->access->isAdmin($suggestion->getBoutique());
        $visible = fn (SuggestionComment $comment): bool => 'public' === $comment->getVisibility()->value
            || ($admin && 'admins' === $comment->getVisibility()->value)
            || ($comment->getUser() === $user && 'private' === $comment->getVisibility()->value)
            || ($suggestion->getCreatedBy() === $user && 'private' === $comment->getVisibility()->value);

        if (isset($uriVariables['id'])) {
            $comment = $this->comments->findOneForSuggestion((string) $uriVariables['id'], $suggestion);

            return $comment instanceof SuggestionComment && $visible($comment) ? $this->mapper->comment($comment) : null;
        }

        return array_map(fn (SuggestionComment $comment) => $this->mapper->comment($comment), array_values(array_filter($this->comments->findBySuggestion($suggestion), $visible)));
    }
}
