<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ChatMarkReadController extends AbstractController
{
    public function __construct(
        private MessageRepository $messageRepository,
    ) {
    }

    #[Route('/boutiques/{boutiqueId}/conversations/{conversationId}/messages/read', name: 'chat_mark_read', methods: ['POST'])]
    public function markAsRead(string $conversationId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $senderType = $data['senderType'] ?? 'admin';

        $this->messageRepository->markAsRead($conversationId, $senderType);

        return $this->json(['success' => true]);
    }
}
