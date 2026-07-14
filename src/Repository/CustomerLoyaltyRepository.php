<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Customer;
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

    public function findOneForCustomer(Customer $customer, Boutique $boutique): ?CustomerLoyalty
    {
        return $this->findOneBy(['customer' => $customer, 'boutique' => $boutique]);
    }

    public function countMembers(Boutique $boutique): int
    {
        return (int) $this->createQueryBuilder('cl')
            ->select('COUNT(cl.id)')
            ->andWhere('cl.boutique = :boutique')
            ->andWhere('cl.totalEarned > 0')
            ->setParameter('boutique', $boutique)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<CustomerLoyalty> */
    public function findTopCustomers(Boutique $boutique, int $limit = 10): array
    {
        return $this->createQueryBuilder('cl')
            ->andWhere('cl.boutique = :boutique')
            ->setParameter('boutique', $boutique)
            ->orderBy('cl.totalEarned', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
