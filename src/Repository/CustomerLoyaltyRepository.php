<?php

namespace App\Repository;

use App\Entity\CustomerLoyalty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CustomerLoyalty> */
final class CustomerLoyaltyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerLoyalty::class);
    }

    public function findOneByCustomerAndBoutique(string $customerId, string $boutiqueId): ?CustomerLoyalty
    {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.customer = :customerId')
            ->andWhere('cl.boutique = :boutiqueId')
            ->setParameter('customerId', $customerId)
            ->setParameter('boutiqueId', $boutiqueId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
