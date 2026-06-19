<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\SubscriptionRequest;
use App\Enum\Subscription\SubscriptionRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionRequest> */
final class SubscriptionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionRequest::class);
    }

    /** @return list<SubscriptionRequest> */
    public function findPendingByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.boutique = :boutique')
            ->andWhere('r.status = :status')
            ->setParameter('boutique', $boutique)
            ->setParameter('status', SubscriptionRequestStatus::Pending)
            ->orderBy('r.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
