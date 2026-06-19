<?php

namespace App\EventSubscriber;

use App\Event\MessageSentEvent;
use App\Service\Chat\MercurePublisher;
use App\Service\Chat\MessageNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MessageEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MercurePublisher $mercurePublisher,
        private MessageNotificationService $notificationService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageSentEvent::class => [
                ['publishToMercure', 10],
                ['sendNotification', 0],
            ],
        ];
    }

    public function publishToMercure(MessageSentEvent $event): void
    {
        $this->mercurePublisher->publishMessage($event->getMessage());
    }

    public function sendNotification(MessageSentEvent $event): void
    {
        $message = $event->getMessage();

        if ('user' === $message->getSenderType()) {
            $this->notificationService->notifyAdmins(
                $message->getConversation(),
                $message->getContent(),
            );
        }
    }
}
