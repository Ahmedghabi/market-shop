<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/** @implements UserProviderInterface<User> */
final class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if ('' === $identifier) {
            throw new UserNotFoundException('Empty identifier.');
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['identifier' => $identifier]);

        if (!$user instanceof User) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
