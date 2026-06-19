<?php

namespace App\Repository;

use App\Entity\ProductFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ProductFilter> */
final class ProductFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductFilter::class);
    }

    /** @return array<ProductFilter> */
    public function findActiveByBoutique(string $boutiqueId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.boutique = :boutiqueId')
            ->andWhere('f.active = :active')
            ->setParameter('boutiqueId', $boutiqueId)
            ->setParameter('active', true)
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
