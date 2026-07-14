<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Suggestion\SuggestionInput;
use App\Dto\Suggestion\SuggestionOfficialResponseInput;
use App\Dto\Suggestion\SuggestionOutput;
use App\Dto\Suggestion\SuggestionStatusInput;
use App\Dto\Suggestion\SuggestionVisibilityInput;
use App\Entity\Suggestion;
use App\Enum\SuggestionStatus;
use App\Enum\SuggestionVisibility;
use App\Repository\SuggestionCategoryRepository;
use App\Repository\SuggestionRepository;
use App\Repository\SuggestionStatusHistoryRepository;
use App\Service\Audit\AuditLogService;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionNotificationService;
use App\Service\Suggestion\SuggestionOutputMapper;
use App\Service\Security\PublicApiRateLimiter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SuggestionProcessor implements ProcessorInterface
{
    public function __construct(
        private SuggestionRepository $suggestions,
        private SuggestionCategoryRepository $categories,
        private SuggestionStatusHistoryRepository $history,
        private SuggestionAccessService $access,
        private SuggestionNotificationService $suggestionNotifications,
        private SuggestionOutputMapper $mapper,
        private AuditLogService $audit,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private PublicApiRateLimiter $rateLimiter,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SuggestionOutput
    {
        $name = $operation->getName() ?? '';
        if ($operation instanceof Delete) {
            $suggestion = $this->find((string) ($uriVariables['id'] ?? ''));
            $this->access->assertCanManage($suggestion, 'suggestion.delete');
            $this->em->remove($suggestion);
            $this->em->flush();
            $this->audit('suggestion.delete', $suggestion);

            return null;
        }

        return match ($name) {
            'suggestion_status' => $this->changeStatus($uriVariables, $data),
            'suggestion_visibility' => $this->changeVisibility($uriVariables, $data),
            'suggestion_publish' => $this->publish($uriVariables),
            'suggestion_official_response' => $this->officialResponse($uriVariables, $data),
            'suggestion_archive' => $this->archive($uriVariables),
            default => isset($uriVariables['id']) ? $this->update((string) $uriVariables['id'], $data) : $this->create($data, $context),
        };
    }

    private function create(mixed $data, array $context): SuggestionOutput
    {
        if (!$data instanceof SuggestionInput) {
            throw new BadRequestHttpException('Invalid suggestion payload.');
        }
        $user = $this->access->requireUser();
        $request = $context['request'] ?? $this->requestStack->getCurrentRequest();
        $boutique = $this->access->resolveBoutique($request);
        $this->access->assertPermission('suggestion.create', $boutique);
        $this->rateLimiter->consumeSuggestion();
        $title = trim((string) $data->title);
        $description = trim((string) $data->description);
        if ('' === $title || '' === $description) {
            throw new BadRequestHttpException('Title and description are required.');
        }

        $entity = new Suggestion($boutique, $user, $title, $description);
        $entity->setStatus(SuggestionStatus::SUBMITTED);
        $this->applyPublicFlags($entity, $data);
        $this->applyCategory($entity, $data->categoryId);
        $this->em->persist($entity);
        $this->em->persist(new \App\Entity\SuggestionStatusHistory($entity, null, SuggestionStatus::SUBMITTED, $user));
        $this->em->flush();
        $this->suggestionNotifications->submitted($entity);
        $this->audit('suggestion.create', $entity);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function update(string $id, mixed $data): SuggestionOutput
    {
        if (!$data instanceof SuggestionInput) {
            throw new BadRequestHttpException('Invalid suggestion payload.');
        }
        $entity = $this->find($id);
        $this->access->assertCanManage($entity, 'suggestion.update');
        if (null !== $data->title) {
            $entity->setTitle(trim($data->title));
        }
        if (null !== $data->description) {
            $entity->setDescription(trim($data->description));
        }
        if (null !== $data->categoryId) {
            $this->applyCategory($entity, $data->categoryId);
        }
        $this->applyPublicFlags($entity, $data);
        $this->em->flush();
        $this->audit('suggestion.update', $entity);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function changeStatus(array $uriVariables, mixed $data): SuggestionOutput
    {
        if (!$data instanceof SuggestionStatusInput || null === $data->status) {
            throw new BadRequestHttpException('A valid status is required.');
        }
        $entity = $this->find((string) ($uriVariables['id'] ?? ''));
        $user = $this->access->assertCanManage($entity, 'suggestion.moderate');
        try {
            $status = SuggestionStatus::from(strtolower($data->status));
        } catch (\ValueError) {
            throw new BadRequestHttpException('Unknown suggestion status.');
        }
        $old = $entity->getStatus();
        if ($old !== $status) {
            if (!$this->isAllowedTransition($old, $status)) {
                throw new BadRequestHttpException(sprintf('Transition de statut impossible: %s vers %s.', $old->value, $status->value));
            }
            $entity->setStatus($status);
            if (SuggestionStatus::IMPLEMENTED === $status) {
                $entity->markClosed();
            }
            if (SuggestionStatus::ARCHIVED === $status) {
                $entity->close();
            }
            if (in_array($status, [SuggestionStatus::REJECTED, SuggestionStatus::ARCHIVED], true)) {
                $entity->unpublish();
            }
            $this->em->persist(new \App\Entity\SuggestionStatusHistory($entity, $old, $status, $user, $data->comment));
        }
        $this->em->flush();
        $this->suggestionNotifications->statusChanged($entity);
        $this->audit('suggestion.status', $entity, ['oldStatus' => $old->value, 'newStatus' => $status->value]);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function changeVisibility(array $uriVariables, mixed $data): SuggestionOutput
    {
        if (!$data instanceof SuggestionVisibilityInput || null === $data->visibility) {
            throw new BadRequestHttpException('A valid visibility is required.');
        }
        $entity = $this->find((string) ($uriVariables['id'] ?? ''));
        $this->access->assertCanManage($entity, 'suggestion.moderate');
        try {
            $entity->setVisibility(SuggestionVisibility::from(strtolower($data->visibility)));
        } catch (\ValueError) {
            throw new BadRequestHttpException('Unknown suggestion visibility.');
        }
        $this->em->flush();
        $this->audit('suggestion.visibility', $entity, ['visibility' => $entity->getVisibility()->value]);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function publish(array $uriVariables): SuggestionOutput
    {
        $entity = $this->find((string) ($uriVariables['id'] ?? ''));
        $this->access->assertCanManage($entity, 'suggestion.publish');
        if (SuggestionStatus::DRAFT === $entity->getStatus() || SuggestionStatus::ARCHIVED === $entity->getStatus()) {
            throw new BadRequestHttpException('This suggestion cannot be published in its current status.');
        }
        $entity->publish();
        $this->em->flush();
        $this->audit('suggestion.publish', $entity);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function officialResponse(array $uriVariables, mixed $data): SuggestionOutput
    {
        if (!$data instanceof SuggestionOfficialResponseInput || null === $data->response) {
            throw new BadRequestHttpException('An official response is required.');
        }
        $entity = $this->find((string) ($uriVariables['id'] ?? ''));
        $user = $this->access->assertCanManage($entity, 'suggestion.moderate');
        $entity->setOfficialResponse(trim($data->response));
        $entity->setOfficialResponseBy($user);
        $this->em->flush();
        $this->suggestionNotifications->officialResponse($entity);
        $this->audit('suggestion.official_response', $entity);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function archive(array $uriVariables): SuggestionOutput
    {
        $entity = $this->find((string) ($uriVariables['id'] ?? ''));
        $user = $this->access->assertCanManage($entity, 'suggestion.moderate');
        $old = $entity->getStatus();
        $entity->close();
        $this->em->persist(new \App\Entity\SuggestionStatusHistory($entity, $old, SuggestionStatus::ARCHIVED, $user));
        $this->em->flush();
        $this->suggestionNotifications->statusChanged($entity);
        $this->audit('suggestion.archive', $entity);

        return $this->mapper->suggestion($entity, false, true);
    }

    private function find(string $id): Suggestion
    {
        $entity = $this->suggestions->find($id);
        if (!$entity instanceof Suggestion) {
            throw new NotFoundHttpException('Suggestion not found.');
        }

        return $entity;
    }

    private function isAllowedTransition(SuggestionStatus $from, SuggestionStatus $to): bool
    {
        return in_array($to, match ($from) {
            SuggestionStatus::DRAFT => [SuggestionStatus::SUBMITTED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::SUBMITTED => [SuggestionStatus::ANALYZING, SuggestionStatus::REJECTED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::ANALYZING => [SuggestionStatus::ACCEPTED, SuggestionStatus::PLANNED, SuggestionStatus::REJECTED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::ACCEPTED => [SuggestionStatus::PLANNED, SuggestionStatus::IN_DEVELOPMENT, SuggestionStatus::REJECTED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::PLANNED => [SuggestionStatus::IN_DEVELOPMENT, SuggestionStatus::REJECTED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::IN_DEVELOPMENT => [SuggestionStatus::IMPLEMENTED, SuggestionStatus::PLANNED, SuggestionStatus::ARCHIVED],
            SuggestionStatus::IMPLEMENTED => [SuggestionStatus::ARCHIVED],
            SuggestionStatus::REJECTED => [SuggestionStatus::ANALYZING, SuggestionStatus::ARCHIVED],
            SuggestionStatus::ARCHIVED => [],
        }, true);
    }

    private function applyCategory(Suggestion $entity, ?string $categoryId): void
    {
        if (null === $categoryId || '' === $categoryId) {
            $entity->setCategory(null);

            return;
        }
        $category = $this->categories->find($categoryId);
        if (!$category || !$category->isActive()) {
            throw new BadRequestHttpException('Suggestion category not found or inactive.');
        }
        $entity->setCategory($category);
    }

    private function applyPublicFlags(Suggestion $entity, SuggestionInput $data): void
    {
        if (null !== $data->showAuthorPublic) {
            $entity->setShowAuthorPublic($data->showAuthorPublic);
        }
        if (null !== $data->showBoutiquePublic) {
            $entity->setShowBoutiquePublic($data->showBoutiquePublic);
        }
    }

    /** @param array<string, mixed> $details */
    private function audit(string $action, Suggestion $entity, array $details = []): void
    {
        $this->audit->log(
            actorEmail: $this->access->requireUser()->getUserIdentifier(),
            actorRole: $this->access->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_BOUTIQUE_ADMIN',
            action: $action,
            resourceType: 'Suggestion',
            resourceId: (string) $entity->getId(),
            details: $details,
            ipAddress: $this->requestStack->getCurrentRequest()?->getClientIp(),
            boutiqueId: (string) $entity->getBoutique()->getId(),
        );
    }
}
