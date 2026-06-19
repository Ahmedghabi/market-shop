<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Chat\MessageResource;
use App\Entity\Message;
use App\Repository\MessageRepository;

/** @implements ProviderInterface<MessageResource> */
final class MessageProvider implements ProviderInterface
{
    public function __construct(
        private MessageRepository $repository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MessageResource|array|null
    {
        $conversationId = $uriVariables['conversationId'] ?? null;

        if (null === $conversationId) {
            return null;
        }

        $id = $uriVariables['id'] ?? null;

        if (null !== $id) {
            return $this->mapSingle($this->repository->findOneBy([
                'id' => $id,
                'conversation' => $conversationId,
            ]));
        }

        $items = $this->repository->findBy(
            ['conversation' => $conversationId],
            ['createdAt' => 'ASC'],
        );

        return array_map(fn (Message $m) => $this->mapSingle($m), $items);
    }

    private function mapSingle(?Message $message): ?MessageResource
    {
        if (null === $message) {
            return null;
        }

        $resource = new MessageResource();
        $resource->id = (string) $message->getId();
        $resource->conversationId = (string) $message->getConversation()->getId();
        $resource->senderType = $message->getSenderType();
        $resource->content = $message->getContent();
        $resource->fileUrl = $message->getFileUrl();
        $resource->fileType = $message->getFileType();
        $resource->read = $message->isRead();
        $resource->createdAt = $message->getCreatedAt()->format('c');
        $resource->readAt = $message->getReadAt()?->format('c');

        return $resource;
    }
}
