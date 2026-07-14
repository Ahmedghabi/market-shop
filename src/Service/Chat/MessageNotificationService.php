<?php

namespace App\Service\Chat;

use App\Entity\Conversation;
use App\Entity\Notification;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MessageNotificationService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function notifyAdmins(Conversation $conversation, string $preview): void
    {
        $boutique = $conversation->getBoutique();
        $admins = $boutique->getUsers();

        $title = sprintf('Nouveau message - %s', $boutique->getName());

        $senderName = $conversation->getUser()?->getDisplayName()
            ?? $conversation->getGuestName()
            ?? 'Client anonyme';

        foreach ($admins as $admin) {
            $notification = new Notification(
                recipientIdentifier: (string) $admin->getUserIdentifier(),
                type: 'chat_message',
                title: $title,
                message: sprintf('%s: %s', $senderName, mb_substr($preview, 0, 200)),
                boutique: $boutique,
            );

            $this->entityManager->persist($notification);
        }

        $superAdmins = $this->userRepository->findByRole('ROLE_SUPER_ADMIN');
        foreach ($superAdmins as $superAdmin) {
            $notification = new Notification(
                recipientIdentifier: (string) $superAdmin->getUserIdentifier(),
                type: 'chat_message',
                title: $title,
                message: sprintf('%s: %s', $senderName, mb_substr($preview, 0, 200)),
                boutique: $boutique,
            );

            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();
    }
}
