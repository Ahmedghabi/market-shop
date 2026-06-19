<?php

namespace App\Controller;

use App\State\Chat\MessageProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ChatFileUploadController extends AbstractController
{
    public function __construct(
        private MessageProcessor $messageProcessor,
    ) {
    }

    #[Route('/chat/upload', name: 'chat_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
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
