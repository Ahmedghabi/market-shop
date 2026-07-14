<?php

namespace App\Repository;

use App\Entity\UserShop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<UserShop> */
final class UserShopRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserShop::class);
    }

    /** @return UserShop[] */
    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    /** @return UserShop[] */
    public function findByBoutique(string $boutiqueId): array
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.boutique = :boutiqueId')
            ->setParameter('boutiqueId', $boutiqueId)
            ->getQuery()
            ->getResult();
    }

    /** @return UserShop[] */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('us')
            ->innerJoin('us.user', 'u')
            ->innerJoin('us.boutique', 'b')
            ->andWhere('us.role = :role')
            ->setParameter('role', $role)
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('u.identifier', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return UserShop[] */
    public function findByRoleAndBoutique(string $role, string $boutiqueId): array
    {
        return $this->createQueryBuilder('us')
            ->innerJoin('us.user', 'u')
            ->innerJoin('us.boutique', 'b')
            ->andWhere('us.role = :role')
            ->andWhere('us.boutique = :boutiqueId')
            ->setParameter('role', $role)
            ->setParameter('boutiqueId', $boutiqueId)
            ->orderBy('u.identifier', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndBoutique(string $userId, string $boutiqueId): ?UserShop
    {
        return $this->createQueryBuilder('us')
            ->andWhere('us.user = :userId')
            ->andWhere('us.boutique = :boutiqueId')
            ->setParameter('userId', $userId)
            ->setParameter('boutiqueId', $boutiqueId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
