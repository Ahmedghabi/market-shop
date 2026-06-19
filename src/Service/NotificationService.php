<?php

namespace App\Service;

use App\Entity\Boutique;
use App\Entity\Notification;
use App\Enum\NotificationChannel;
use App\Message\DispatchNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
    ) {
    }

    public function notify(?string $recipientIdentifier, string $type, string $title, string $message, ?Boutique $boutique = null): void
    {
        $this->entityManager->persist(new Notification($recipientIdentifier, $type, $title, $message, $boutique));
    }

    /** @param array<string, string|int|float|bool|null> $variables */
    public function dispatchExternal(?Boutique $boutique, string $eventCode, NotificationChannel $channel, string $recipient, array $variables = []): void
    {
        $this->bus->dispatch(new DispatchNotificationMessage(
            $boutique ? (string) $boutique->getId() : null,
            $eventCode,
            $channel->value,
            $recipient,
            $variables,
        ));
    }
}
