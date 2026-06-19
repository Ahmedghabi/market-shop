<?php

namespace App\Repository;

use App\Entity\Subscription;
use App\Enum\SubscriptionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Subscription> */
final class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    /** @return list<Subscription> */
    public function findActiveExpiringBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.endDate IS NOT NULL')
            ->andWhere('s.endDate >= :from')
            ->andWhere('s.endDate <= :to')
            ->setParameter('status', SubscriptionStatus::Active)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Subscription> */
    public function findActiveExpired(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->andWhere('s.endDate IS NOT NULL')
            ->andWhere('s.endDate <= :now')
            ->setParameter('status', SubscriptionStatus::Active)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
