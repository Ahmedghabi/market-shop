<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Metadata\Operation;
use App\Dto\Suggestion\SuggestionReactionInput;
use App\Dto\Suggestion\SuggestionReactionOutput;
use App\Entity\SuggestionReaction;
use App\Enum\SuggestionReactionType;
use App\Repository\SuggestionReactionRepository;
use App\Repository\SuggestionRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionNotificationService;
use App\Service\Suggestion\SuggestionOutputMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SuggestionReactionProcessor implements ProcessorInterface
{
    public function __construct(private SuggestionRepository $suggestions, private SuggestionReactionRepository $reactions, private SuggestionAccessService $access, private SuggestionNotificationService $notifications, private SuggestionOutputMapper $mapper, private AuditLogService $audit, private EntityManagerInterface $em)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SuggestionReactionOutput
    {
        $suggestion = $this->suggestions->find($uriVariables['suggestionId'] ?? '');
        if (!$suggestion) {
            throw new NotFoundHttpException('Suggestion not found.');
        }
        $user = $this->access->assertCanInteract($suggestion, 'suggestion.react');
        $reaction = $this->reactions->findOneBySuggestionAndUser($suggestion, $user);
        $wasExisting = $reaction instanceof SuggestionReaction;

        if ($operation instanceof Delete) {
            if ($reaction) {
                $this->em->remove($reaction);
                $this->em->flush();
                $this->writeAudit('suggestion.reaction.delete', $suggestion);
            }

            return null;
        }
        if (!$data instanceof SuggestionReactionInput || null === $data->type) {
            throw new BadRequestHttpException('A reaction type is required.');
        }
        try {
            $type = SuggestionReactionType::from(strtolower($data->type));
        } catch (\ValueError) {
            throw new BadRequestHttpException('Unknown reaction type.');
        }
        if ($reaction) {
            $reaction->setType($type);
        } else {
            $reaction = new SuggestionReaction($suggestion, $user, $suggestion->getBoutique(), $type);
            $this->em->persist($reaction);
        }
        $this->em->flush();
        $this->notifications->reaction($reaction);
        $this->writeAudit('suggestion.reaction.'.($wasExisting ? 'update' : 'create'), $suggestion);

        return $this->mapper->reaction($reaction);
    }

    private function writeAudit(string $action, \App\Entity\Suggestion $suggestion): void
    {
        $this->audit->log($this->access->requireUser()->getUserIdentifier(), $this->access->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_USER', $action, 'SuggestionReaction', (string) $suggestion->getId(), null, null, (string) $suggestion->getBoutique()->getId());
    }
}
