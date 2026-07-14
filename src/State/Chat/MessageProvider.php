<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Chat\MessageResource;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\Chat\ChatAccessService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @implements ProviderInterface<MessageResource> */
final class MessageProvider implements ProviderInterface
{
    public function __construct(
        private MessageRepository $repository,
        private ConversationRepository $conversationRepository,
        private ChatAccessService $access,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MessageResource|array|null
    {
        $conversationId = $uriVariables['conversationId'] ?? null;

        if (null === $conversationId) {
            return null;
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation instanceof Conversation) {
            return null;
        }

        if (!$this->access->canAccessConversation($conversation, $this->getGuestToken($context))) {
            throw new AccessDeniedHttpException('Conversation access denied');
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

    private function getGuestToken(array $context): ?string
    {
        $request = $context['request'] ?? null;

        return $request instanceof Request ? $request->headers->get('X-Guest-Chat-Token') : null;
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
