<?php

namespace App\Service\Chat;

use App\Entity\Message;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

final class MercurePublisher
{
    public function __construct(
        private HubInterface $hub,
        private LoggerInterface $logger,
    ) {
    }

    public function publishMessage(Message $message): void
    {
        $conversationId = (string) $message->getConversation()->getId();
        $boutiqueId = (string) $message->getConversation()->getBoutique()->getId();

        $data = json_encode([
            'id' => (string) $message->getId(),
            'conversationId' => $conversationId,
            'senderType' => $message->getSenderType(),
            'content' => $message->getContent(),
            'fileUrl' => $message->getFileUrl(),
            'fileType' => $message->getFileType(),
            'createdAt' => $message->getCreatedAt()->format('c'),
        ], JSON_THROW_ON_ERROR);

        $topics = [
            sprintf('chat/conversation/%s', $conversationId),
            sprintf('chat/boutique/%s', $boutiqueId),
        ];

        foreach ($topics as $topic) {
            $update = new Update($topic, $data);
            $this->publish($update);
        }
    }

    public function publishTyping(string $conversationId, string $senderType): void
    {
        $data = json_encode([
            'type' => 'typing',
            'conversationId' => $conversationId,
            'senderType' => $senderType,
        ], JSON_THROW_ON_ERROR);

        $update = new Update(sprintf('chat/conversation/%s', $conversationId), $data);
        $this->publish($update);
    }

    public function markAsRead(Message $message): void
    {
        $data = json_encode([
            'type' => 'read',
            'messageId' => (string) $message->getId(),
            'conversationId' => (string) $message->getConversation()->getId(),
        ], JSON_THROW_ON_ERROR);

        $update = new Update(sprintf('chat/conversation/%s', (string) $message->getConversation()->getId()), $data);
        $this->publish($update);
    }

    private function publish(Update $update): void
    {
        try {
            $this->hub->publish($update);
        } catch (\Throwable $exception) {
            $this->logger->warning('Mercure chat publish failed.', [
                'exception' => $exception,
            ]);
        }
    }
}
