<?php

namespace App\Controller\Rest\Auth;

use App\Service\Session\SessionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SessionController
{
    public function __construct(private SessionService $sessions)
    {
    }

    #[Route('/api/me/sessions', name: 'api_me_sessions', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse(['items' => $this->sessions->listForCurrentUser()]);
    }

    #[Route('/api/me/sessions/{id}', name: 'api_me_session_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $this->sessions->deleteSession($id);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/me/sessions', name: 'api_me_sessions_delete_all', methods: ['DELETE'])]
    public function deleteAll(): JsonResponse
    {
        $this->sessions->deleteAllSessions();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
