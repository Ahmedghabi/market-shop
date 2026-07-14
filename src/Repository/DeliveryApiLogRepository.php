<?php

namespace App\Repository;

use App\Entity\DeliveryApiLog;
use App\Entity\DeliveryCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<DeliveryApiLog> */
final class DeliveryApiLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryApiLog::class);
    }

    /** @return list<DeliveryApiLog> */
    public function findRecent(int $limit = 100, ?DeliveryCompany $company = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit);

        if (null !== $company) {
            $qb->andWhere('l.deliveryCompany = :company')->setParameter('company', $company);
        }

        return $qb->getQuery()->getResult();
    }
}
