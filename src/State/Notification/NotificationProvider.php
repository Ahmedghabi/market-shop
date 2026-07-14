<?php

namespace App\State\Notification;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Notification\NotificationResource;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<NotificationResource> */
final class NotificationProvider implements ProviderInterface, ProcessorInterface
{
    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<NotificationResource>|NotificationResource|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|NotificationResource|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        $recipient = $this->context->getUserIdentifier();
        $isSuperAdmin = $this->context->isSuperAdmin();

        $entities = $this->repository->findForRecipient($recipient, $isSuperAdmin);

        return array_map([$this, 'toOutput'], $entities);
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): NotificationResource
    {
        $id = $uriVariables['id'] ?? '';
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Notification not found');
        }
        if (!$this->context->isSuperAdmin() && $entity->getRecipientIdentifier() !== $this->context->getUserIdentifier()) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $entity->markAsRead();
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function toOutput(Notification $entity): NotificationResource
    {
        $output = new NotificationResource();
        $output->id = (string) $entity->getId();
        $output->recipientIdentifier = $entity->getRecipientIdentifier();
        $output->type = $entity->getType();
        $output->title = $entity->getTitle();
        $output->message = $entity->getMessage();
        $output->boutiqueId = null !== $entity->getBoutique() ? (string) $entity->getBoutique()->getId() : null;
        $output->read = $entity->isRead();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
