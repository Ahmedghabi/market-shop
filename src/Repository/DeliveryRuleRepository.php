<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\DeliveryRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<DeliveryRule> */
final class DeliveryRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryRule::class);
    }

    /** @return list<DeliveryRule> */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('dr')
            ->andWhere('dr.boutique = :boutique')
            ->andWhere('dr.isActive = true')
            ->setParameter('boutique', $boutique)
            ->orderBy('dr.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<DeliveryRule> */
    public function findByBoutique(Boutique $boutique, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('dr')
            ->andWhere('dr.boutique = :boutique')
            ->setParameter('boutique', $boutique)
            ->orderBy('dr.priority', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
