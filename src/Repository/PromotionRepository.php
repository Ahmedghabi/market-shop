<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Promotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Promotion> */
final class PromotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Promotion::class);
    }

    /** @return list<Promotion> */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'pc')->addSelect('pc')
            ->leftJoin('p.products', 'pp')->addSelect('pp')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('boutique', $boutique)
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Promotion> */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'pc')->addSelect('pc')
            ->leftJoin('p.products', 'pp')->addSelect('pp')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.deletedAt IS NULL')
            ->andWhere('p.active = true')
            ->andWhere('p.startsAt <= :now')
            ->andWhere('p.endsAt IS NULL OR p.endsAt >= :now')
            ->setParameter('boutique', $boutique)
            ->setParameter('now', $now)
            ->orderBy('p.priority', 'DESC')
            ->addOrderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
