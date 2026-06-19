<?php

namespace App\Controller\Rest;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class NotificationController
{
    public function __construct(
        private NotificationRepository $notifications,
        private Security $security,
    ) {
    }

    #[Route('/api/notifications', name: 'api_notifications', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        $items = $this->notifications->findForRecipient($user?->getUserIdentifier(), $this->security->isGranted('ROLE_SUPER_ADMIN'));

        return new JsonResponse([
            'items' => array_map(static fn (Notification $notification): array => [
                'id' => (string) $notification->getId(),
                'type' => $notification->getType(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'boutiqueId' => $notification->getBoutique() ? (string) $notification->getBoutique()->getId() : null,
                'read' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format(DATE_ATOM),
            ], $items),
        ]);
    }
}
