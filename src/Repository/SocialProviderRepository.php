<?php

namespace App\Repository;

use App\Entity\SocialProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SocialProvider> */
final class SocialProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialProvider::class);
    }

    /** @return SocialProvider[] */
    public function findActive(): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
