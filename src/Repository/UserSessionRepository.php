<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserSession> */
final class UserSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSession::class);
    }

    /** @return list<UserSession> */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.isActive = true')
            ->orderBy('s.lastActivityAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findOneByTokenId(string $tokenId): ?UserSession
    {
        return $this->findOneBy(['tokenId' => $tokenId]);
    }

    public function deactivateOthers(User $user, string $excludeTokenId): int
    {
        return $this->createQueryBuilder('s')
            ->update()
            ->set('s.isActive', ':inactive')
            ->set('s.isCurrent', ':current')
            ->andWhere('s.user = :user')
            ->andWhere('s.tokenId != :tokenId')
            ->setParameter('inactive', false)
            ->setParameter('current', false)
            ->setParameter('user', $user)
            ->setParameter('tokenId', $excludeTokenId)
            ->getQuery()
            ->execute();
    }

    public function deactivateAll(User $user): int
    {
        return $this->createQueryBuilder('s')
            ->update()
            ->set('s.isActive', ':inactive')
            ->set('s.isCurrent', ':current')
            ->andWhere('s.user = :user')
            ->setParameter('inactive', false)
            ->setParameter('current', false)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function deleteExpired(\DateTimeImmutable $now): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->andWhere('s.expiresAt <= :now OR s.isActive = false')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }
}
