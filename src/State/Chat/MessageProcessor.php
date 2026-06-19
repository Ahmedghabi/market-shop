<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Chat\MessageResource;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Event\MessageSentEvent;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\ImageService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/** @implements ProcessorInterface<MessageResource> */
final class MessageProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageRepository $repository,
        private ConversationRepository $conversationRepository,
        private EventDispatcherInterface $eventDispatcher,
        private ImageService $imageService,
        private TokenStorageInterface $tokenStorage,
        private string $uploadDir,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MessageResource
    {
        $conversationId = $uriVariables['conversationId'] ?? null;
        $conversation = $this->conversationRepository->find($conversationId);

        if (!$conversation instanceof Conversation) {
            throw new NotFoundHttpException('Conversation not found');
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        $isAdmin = null !== $user && in_array('ROLE_BOUTIQUE_ADMIN', $user->getRoles(), true);

        $senderType = $isAdmin ? 'admin' : ($data->senderType ?? 'user');

        $message = new Message($conversation, $senderType, $data->content ?? '');

        if (!empty($data->fileUrl)) {
            $message->setFileUrl($data->fileUrl);
            $message->setFileType($data->fileType ?? 'file');
        }

        $this->repository->save($message, true);

        $conversation->touch();
        $this->conversationRepository->save($conversation, true);

        $this->eventDispatcher->dispatch(new MessageSentEvent($message));

        return $this->mapToResource($message);
    }

    public function handleFileUpload(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);

        if ($isImage) {
            $paths = $this->imageService->uploadAndResize($file, 'chat');

            return [
                'url' => $paths['largeUrl'],
                'thumbnailUrl' => $paths['smallUrl'],
                'type' => 'image',
            ];
        }

        $filename = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);
        $relativeDir = sprintf('uploads/chat/%s', date('Y/m'));
        $absoluteDir = sprintf('%s/%s', $this->uploadDir, $relativeDir);

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $file->move($absoluteDir, $filename);

        return [
            'url' => sprintf('/%s/%s', $relativeDir, $filename),
            'type' => $file->getClientMimeType() ?? 'file',
        ];
    }

    private function mapToResource(Message $message): MessageResource
    {
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
