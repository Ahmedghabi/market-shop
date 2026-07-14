<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\Chat\ChatAccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class ChatMarkReadController extends AbstractController
{
    public function __construct(
        private MessageRepository $messageRepository,
        private ConversationRepository $conversationRepository,
        private ChatAccessService $access,
    ) {
    }

    #[Route('/boutiques/{boutiqueId}/conversations/{conversationId}/messages/read', name: 'chat_mark_read', methods: ['POST'])]
    #[Route('/conversations/{conversationId}/messages/read', name: 'chat_mark_read_flat', methods: ['POST'])]
    public function markAsRead(string $conversationId, Request $request): JsonResponse
    {
        $conversation = $this->conversationRepository->find($conversationId);
        if (!$conversation instanceof Conversation) {
            throw new NotFoundHttpException('Conversation not found');
        }

        if (!$this->access->canAccessConversation($conversation, $request->headers->get('X-Guest-Chat-Token'))) {
            throw new AccessDeniedHttpException('Conversation access denied');
        }

        $data = json_decode($request->getContent(), true);
        $senderType = $this->access->isAdminResponder() ? 'admin' : ($data['senderType'] ?? 'user');

        $this->messageRepository->markAsRead($conversationId, $senderType);

        return $this->json(['success' => true]);
    }
}
