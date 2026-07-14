<?php

namespace App\Service\Chat;

use App\Entity\Conversation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class ChatAccessService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function canAccessConversation(Conversation $conversation, ?string $guestToken = null): bool
    {
        $user = $this->getCurrentUser();

        if ($user instanceof User) {
            if ($this->isSuperAdmin($user)) {
                return true;
            }

            if ($this->isBoutiqueAdmin($user) && $user->getAdministeredBoutiques()->contains($conversation->getBoutique())) {
                return true;
            }

            if ((string) $conversation->getUser()?->getId() === (string) $user->getId()) {
                return true;
            }
        }

        return $conversation->isGuestAccessTokenValid($guestToken);
    }

    public function isAdminResponder(): bool
    {
        $user = $this->getCurrentUser();

        return $user instanceof User && ($this->isSuperAdmin($user) || $this->isBoutiqueAdmin($user));
    }

    public function canManageAllConversations(): bool
    {
        $user = $this->getCurrentUser();

        return $user instanceof User && $this->isSuperAdmin($user);
    }

    public function getAdministeredBoutiques(): array
    {
        $user = $this->getCurrentUser();

        if (!$user instanceof User || (!$this->isSuperAdmin($user) && !$this->isBoutiqueAdmin($user))) {
            return [];
        }

        return $user->getAdministeredBoutiques()->toArray();
    }

    public function getCurrentUser(): mixed
    {
        return $this->tokenStorage->getToken()?->getUser();
    }

    private function isSuperAdmin(User $user): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true);
    }

    private function isBoutiqueAdmin(User $user): bool
    {
        return in_array('ROLE_BOUTIQUE_ADMIN', $user->getRoles(), true);
    }
}
