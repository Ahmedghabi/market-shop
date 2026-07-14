<?php

namespace App\Repository;

use App\Entity\DeliveryCompany;
use App\Entity\DeliveryEndpoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<DeliveryEndpoint> */
final class DeliveryEndpointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryEndpoint::class);
    }

    /** @return list<DeliveryEndpoint> */
    public function findByCompany(DeliveryCompany $company): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.company = :company')
            ->setParameter('company', $company)
            ->orderBy('e.type', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
