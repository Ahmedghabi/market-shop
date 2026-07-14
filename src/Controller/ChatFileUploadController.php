<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Service\Chat\ChatAccessService;
use App\State\Chat\MessageProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ChatFileUploadController extends AbstractController
{
    public function __construct(
        private MessageProcessor $messageProcessor,
        private ConversationRepository $conversationRepository,
        private ChatAccessService $access,
    ) {
    }

    #[Route('/chat/upload', name: 'chat_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $conversationId = $request->request->get('conversationId');
        if (!is_string($conversationId) || '' === $conversationId) {
            throw new BadRequestHttpException('Conversation is required');
        }

        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation instanceof Conversation) {
            throw new NotFoundHttpException('Conversation not found');
        }

        if (!$this->access->canAccessConversation($conversation, $request->headers->get('X-Guest-Chat-Token'))) {
            throw new AccessDeniedHttpException('Conversation access denied');
        }

        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->json(['error' => 'Invalid file'], 400);
        }

        $allowedMimeTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'application/zip',
        ];

        if (!in_array($file->getClientMimeType(), $allowedMimeTypes, true)) {
            return $this->json(['error' => 'File type not allowed'], 400);
        }

        $maxSize = 10 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            return $this->json(['error' => 'File too large (max 10MB)'], 400);
        }

        $result = $this->messageProcessor->handleFileUpload($file);

        return $this->json($result);
    }
}
