<?php

namespace App\Security;

use App\Entity\Boutique;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Uuid;

final readonly class BoutiqueContext
{
    public function __construct(
        private Security $security,
        private UserRepository $users,
    ) {
    }

    public function isSuperAdmin(): bool
    {
        return $this->security->isGranted('ROLE_SUPER_ADMIN');
    }

    public function getBoutiqueId(): ?Uuid
    {
        $boutiqueIds = $this->getBoutiqueIds();

        return $boutiqueIds[0] ?? null;
    }

    /** @return list<Uuid> */
    public function getBoutiqueIds(): array
    {
        $user = $this->security->getUser();
        if (null === $user) {
            return [];
        }

        $appUser = $this->users->findOneBy(['identifier' => $user->getUserIdentifier()]);

        if (null === $appUser) {
            return [];
        }

        return array_values(array_map(
            static fn (Boutique $boutique): Uuid => $boutique->getId(),
            $appUser->getAdministeredBoutiques()->toArray(),
        ));
    }

    public function getUserIdentifier(): ?string
    {
        return $this->security->getUser()?->getUserIdentifier();
    }

    public function canAccessBoutique(Boutique $boutique): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->getBoutiqueIds() as $boutiqueId) {
            if ((string) $boutiqueId === (string) $boutique->getId()) {
                return true;
            }
        }

        return false;
    }
}
