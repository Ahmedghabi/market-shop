<?php

namespace App\State\ExtensionRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ExtensionRequest\ExtensionRequestInput;
use App\Dto\ExtensionRequest\ExtensionRequestOutput;
use App\Entity\Boutique;
use App\Entity\BoutiqueExtension;
use App\Entity\Extension;
use App\Entity\ExtensionRequest;
use App\Enum\ExtensionRequestStatus;
use App\Repository\ExtensionRepository;
use App\Repository\ExtensionRequestRepository;
use App\Repository\UserRepository;
use App\Security\BoutiqueContext;
use App\Service\Audit\AuditLogService;
use App\Service\NotificationService;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ExtensionRequestProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private readonly ExtensionRequestRepository $repository,
        private readonly ExtensionRepository $extensions,
        private readonly UserRepository $users,
        private readonly BoutiqueContext $context,
        private readonly NotificationService $notifications,
        private readonly AuditLogService $auditLog,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ExtensionRequestOutput
    {
        $operationName = $operation->getName() ?? '';

        return match ($operationName) {
            'pay_extension_request' => $this->pay((string) ($uriVariables['id'] ?? '')),
            'cancel_extension_request' => $this->cancel((string) ($uriVariables['id'] ?? '')),
            'approve_extension_request' => $this->approve((string) ($uriVariables['id'] ?? ''), $data),
            'reject_extension_request' => $this->reject((string) ($uriVariables['id'] ?? ''), $data),
            'suspend_extension_request' => $this->suspend((string) ($uriVariables['id'] ?? ''), $data),
            default => $this->create($data, $uriVariables, $context),
        };
    }

    private function create(mixed $data, array $uriVariables, array $context): ExtensionRequestOutput
    {
        if (!$data instanceof ExtensionRequestInput) {
            throw new \InvalidArgumentException('Expected ExtensionRequestInput');
        }

        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);
        $extension = $this->findExtension($data->extensionId);

        $entity = new ExtensionRequest(
            boutique: $boutique,
            extension: $extension,
            priceTnd: $extension->getPriceTnd(),
            comment: $data->comment,
        );
        $entity->initializeWorkflow();
        $this->em->persist($entity);
        $this->em->flush();

        $this->notifyBoutique($boutique, 'extension_requested', 'Demande envoyee', sprintf('Votre demande pour "%s" a ete enregistree.', $extension->getName()));
        $this->notifySuperAdmins('extension_request_new', 'Nouvelle demande d\'extension', sprintf('%s a demande l\'extension "%s".', $boutique->getName(), $extension->getName()));
        $this->audit($boutique, 'extension_request.created', $entity);

        // Free + no-validation-required extensions activate immediately, without waiting on a SUPER_ADMIN.
        if (0 === $entity->getPriceTnd() && !$extension->requiresValidation()) {
            $this->activate($entity, 'system');
            $this->em->flush();
        }

        return $this->toProvider()->toOutput($entity);
    }

    private function pay(string $id): ExtensionRequestOutput
    {
        $entity = $this->findOwnedEntity($id);

        if (ExtensionRequestStatus::AwaitingPayment !== $entity->getStatus()) {
            throw new \InvalidArgumentException('This request is not awaiting payment.');
        }

        $entity->markPaid();

        $extension = $entity->getExtension();
        if (!$extension->requiresValidation()) {
            $this->activate($entity, 'system');
        }

        $this->em->flush();

        $this->notifyBoutique($entity->getBoutique(), 'extension_paid', 'Paiement recu', sprintf('Le paiement pour "%s" a ete confirme.', $extension->getName()));
        $this->notifySuperAdmins('extension_request_paid', 'Paiement recu', sprintf('%s a paye pour l\'extension "%s".', $entity->getBoutique()->getName(), $extension->getName()));
        $this->audit($entity->getBoutique(), 'extension_request.paid', $entity);

        return $this->toProvider()->toOutput($entity);
    }

    private function cancel(string $id): ExtensionRequestOutput
    {
        $entity = $this->findOwnedEntity($id);
        $entity->cancel();
        $this->em->flush();

        $this->audit($entity->getBoutique(), 'extension_request.cancelled', $entity);

        return $this->toProvider()->toOutput($entity);
    }

    private function approve(string $id, mixed $data): ExtensionRequestOutput
    {
        $entity = $this->findEntity($id);
        $adminComment = $this->extractAdminComment($data);

        $this->activate($entity, $this->context->getUserIdentifier() ?? 'super-admin', $adminComment);
        $this->em->flush();

        $this->notifyBoutique($entity->getBoutique(), 'extension_activated', 'Extension activee', sprintf('"%s" est maintenant active sur votre boutique.', $entity->getExtension()->getName()));
        $this->audit($entity->getBoutique(), 'extension_request.approved', $entity);

        return $this->toProvider()->toOutput($entity);
    }

    private function reject(string $id, mixed $data): ExtensionRequestOutput
    {
        $entity = $this->findEntity($id);
        $adminComment = $this->extractAdminComment($data);

        $entity->reject($this->context->getUserIdentifier() ?? 'super-admin', $adminComment);
        $this->em->flush();

        $this->notifyBoutique($entity->getBoutique(), 'extension_rejected', 'Demande refusee', sprintf('Votre demande pour "%s" a ete refusee.', $entity->getExtension()->getName()));
        $this->audit($entity->getBoutique(), 'extension_request.rejected', $entity);

        return $this->toProvider()->toOutput($entity);
    }

    private function suspend(string $id, mixed $data): ExtensionRequestOutput
    {
        $entity = $this->findEntity($id);
        $adminComment = $this->extractAdminComment($data);

        $entity->suspend($this->context->getUserIdentifier() ?? 'super-admin', $adminComment);
        $entity->getGrant()?->deactivate();
        $this->em->flush();

        $this->notifyBoutique($entity->getBoutique(), 'extension_suspended', 'Extension suspendue', sprintf('"%s" a ete suspendue.', $entity->getExtension()->getName()));
        $this->audit($entity->getBoutique(), 'extension_request.suspended', $entity);

        return $this->toProvider()->toOutput($entity);
    }

    private function activate(ExtensionRequest $entity, string $actor, ?string $adminComment = null): void
    {
        $extension = $entity->getExtension();
        $expiresAt = null !== $extension->getDurationMonths()
            ? (new \DateTimeImmutable())->modify(sprintf('+%d months', $extension->getDurationMonths()))
            : null;

        $grant = new BoutiqueExtension(
            boutique: $entity->getBoutique(),
            extension: $extension,
            expiresAt: $expiresAt,
            activatedBy: $actor,
        );
        $this->em->persist($grant);

        $entity->approve($actor, $grant, $adminComment);
    }

    private function findExtension(string $id): Extension
    {
        $entity = $this->extensions->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Extension not found');
        }

        return $entity;
    }

    private function findEntity(string $id): ExtensionRequest
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Extension request not found');
        }

        return $entity;
    }

    private function findOwnedEntity(string $id): ExtensionRequest
    {
        $entity = $this->findEntity($id);
        if (!$this->context->canAccessBoutique($entity->getBoutique())) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return $entity;
    }

    private function extractAdminComment(mixed $data): ?string
    {
        return \is_object($data) && property_exists($data, 'adminComment') && \is_string($data->adminComment)
            ? $data->adminComment
            : null;
    }

    private function notifyBoutique(Boutique $boutique, string $type, string $title, string $message): void
    {
        $this->notifications->notify(null, $type, $title, $message, $boutique);
    }

    private function notifySuperAdmins(string $type, string $title, string $message): void
    {
        foreach ($this->users->findByRole('ROLE_SUPER_ADMIN') as $admin) {
            $this->notifications->notify($admin->getUserIdentifier(), $type, $title, $message);
        }
    }

    private function audit(Boutique $boutique, string $action, ExtensionRequest $entity): void
    {
        $this->auditLog->log(
            actorEmail: $this->context->getUserIdentifier() ?? 'system',
            actorRole: $this->context->isSuperAdmin() ? 'ROLE_SUPER_ADMIN' : 'ROLE_BOUTIQUE_ADMIN',
            action: $action,
            resourceType: 'ExtensionRequest',
            resourceId: (string) $entity->getId(),
            details: [
                'extensionCode' => $entity->getExtension()->getCode(),
                'status' => $entity->getStatus()->value,
                'priceTnd' => $entity->getPriceTnd(),
            ],
            boutiqueId: (string) $boutique->getId(),
        );
    }

    private function toProvider(): ExtensionRequestProvider
    {
        return new ExtensionRequestProvider($this->repository, $this->context);
    }
}
