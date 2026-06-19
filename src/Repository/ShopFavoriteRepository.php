<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\ShopFavorite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ShopFavorite> */
final class ShopFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopFavorite::class);
    }

    /** @return list<ShopFavorite> */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['createdAt' => 'DESC']);
    }

    /** @return list<ShopFavorite> */
    public function findBySession(string $sessionId): array
    {
        return $this->findBy(['sessionId' => $sessionId], ['createdAt' => 'DESC']);
    }

    public function findOneByUserAndBoutique(User $user, Boutique $boutique): ?ShopFavorite
    {
        return $this->findOneBy(['user' => $user, 'boutique' => $boutique]);
    }

    public function findOneBySessionAndBoutique(string $sessionId, Boutique $boutique): ?ShopFavorite
    {
        return $this->findOneBy(['sessionId' => $sessionId, 'boutique' => $boutique]);
    }

    public function deleteBySession(string $sessionId): void
    {
        $this->createQueryBuilder('f')
            ->delete()
            ->andWhere('f.sessionId = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->execute();
    }
}
