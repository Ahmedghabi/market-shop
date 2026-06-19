<?php

namespace App\Controller\Rest;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class UsersController
{
    public function __construct(
        private UserRepository $users,
        private Security $security,
    ) {
    }

    #[Route('/api/users', name: 'api_users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        if (!$this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return new JsonResponse(['message' => 'Seul le Super Admin peut lister les utilisateurs.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $all = $this->users->findAll();
        $result = array_map(static fn (User $user): array => [
            'id' => (string) $user->getId(),
            'email' => $user->getUserIdentifier(),
            'displayName' => $user->getDisplayName(),
            'roles' => $user->getRoles(),
            'boutiqueId' => $user->getBoutique() ? (string) $user->getBoutique()->getId() : null,
            'boutiqueName' => $user->getBoutique()?->getName(),
        ], $all);

        return new JsonResponse(['users' => $result]);
    }
}
