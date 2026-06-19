<?php

namespace App\Repository;

use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionPlan> */
final class SubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }

    /** @return list<SubscriptionPlan> */
    public function findVisibleForBoutique(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->andWhere('p.isVisible = :visible')
            ->orderBy('p.priceTnd', 'ASC')
            ->setParameter('active', true)
            ->setParameter('visible', true)
            ->getQuery()
            ->getResult();
    }
}
