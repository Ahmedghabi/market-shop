<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Suggestion\SuggestionCommentInput;
use App\Dto\Suggestion\SuggestionCommentOutput;
use App\Entity\SuggestionComment;
use App\Enum\SuggestionVisibility;
use App\Repository\SuggestionCommentRepository;
use App\Repository\SuggestionRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionNotificationService;
use App\Service\Suggestion\SuggestionOutputMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SuggestionCommentProcessor implements ProcessorInterface
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionCommentRepository $comments, private SuggestionAccessService $access, private SuggestionNotificationService $notifications, private SuggestionOutputMapper $mapper, private AuditLogService $audit, private EntityManagerInterface $em)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SuggestionCommentOutput
    {
        $commentId = $uriVariables['id'] ?? null;
        $existingComment = null;
        if (!isset($uriVariables['suggestionId']) && null !== $commentId) {
            $existingComment = $this->comments->find($commentId);
        }
        $suggestion = $existingComment?->getSuggestion() ?? $this->suggestions->find($uriVariables['suggestionId'] ?? '');
        if (!$suggestion) {
            throw new NotFoundHttpException('Suggestion not found.');
        }
        $user = $this->access->assertCanInteract($suggestion, 'suggestion.comment');
        $id = $uriVariables['id'] ?? null;

        if ($operation instanceof Delete) {
            $comment = $existingComment instanceof SuggestionComment ? $existingComment : $this->comments->findOneForSuggestion((string) $id, $suggestion);
            if (!$comment) {
                throw new NotFoundHttpException('Comment not found.');
            }
            if ($comment->getUser() !== $user) {
                $this->access->assertPermission('suggestion.moderate', $suggestion->getBoutique());
            }
            $this->em->remove($comment);
            $this->em->flush();
            $this->writeAudit('suggestion.comment.delete', $suggestion);

            return null;
        }

        if (null !== $id) {
            $comment = $existingComment instanceof SuggestionComment ? $existingComment : $this->comments->findOneForSuggestion((string) $id, $suggestion);
            if (!$comment) {
                throw new NotFoundHttpException('Comment not found.');
            }
            if ($comment->getUser() !== $user) {
                $this->access->assertPermission('suggestion.moderate', $suggestion->getBoutique());
            }
            if (!$data instanceof SuggestionCommentInput) {
                throw new BadRequestHttpException('Invalid comment payload.');
            }
            if (null !== $data->content) {
                $comment->setContent(trim($data->content));
            }
            if (null !== $data->visibility) {
                $comment->setVisibility($this->visibility($data->visibility));
            }
            $this->em->flush();
            $this->writeAudit('suggestion.comment.update', $suggestion);

            return $this->mapper->comment($comment);
        }

        if (!$data instanceof SuggestionCommentInput || null === $data->content || '' === trim($data->content)) {
            throw new BadRequestHttpException('Comment content is required.');
        }
        $visibility = null === $data->visibility ? SuggestionVisibility::PUBLIC : $this->visibility($data->visibility);
        $parent = null;
        if (null !== $data->parentId && '' !== $data->parentId) {
            $parent = $this->comments->findOneForSuggestion($data->parentId, $suggestion);
            if (!$parent) {
                throw new BadRequestHttpException('Parent comment not found.');
            }
        }
        $comment = new SuggestionComment($suggestion, $user, $suggestion->getBoutique(), trim($data->content), $visibility, $parent);
        $this->em->persist($comment);
        $this->em->flush();
        $this->notifications->comment($comment);
        $this->writeAudit('suggestion.comment.create', $suggestion);

        return $this->mapper->comment($comment);
    }

    private function visibility(string $value): SuggestionVisibility
    {
        try {
            return SuggestionVisibility::from(strtolower($value));
        } catch (\ValueError) {
            throw new BadRequestHttpException('Unknown comment visibility.');
        }
    }

    private function writeAudit(string $action, \App\Entity\Suggestion $suggestion): void
    {
        $this->audit->log($this->access->requireUser()->getUserIdentifier(), $this->access->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_USER', $action, 'SuggestionComment', (string) $suggestion->getId(), null, null, (string) $suggestion->getBoutique()->getId());
    }
}
