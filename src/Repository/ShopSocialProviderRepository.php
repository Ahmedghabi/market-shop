<?php

namespace App\Repository;

use App\Entity\ShopSocialProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ShopSocialProvider> */
final class ShopSocialProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopSocialProvider::class);
    }

    /** @return ShopSocialProvider[] */
    public function findActiveByBoutique(string $boutiqueId): array
    {
        return $this->createQueryBuilder('ssp')
            ->andWhere('ssp.boutique = :boutiqueId')
            ->andWhere('ssp.isActive = :active')
            ->setParameter('boutiqueId', $boutiqueId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
