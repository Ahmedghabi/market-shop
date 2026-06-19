<?php

namespace App\Repository;

use App\Entity\CustomerAuthProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CustomerAuthProvider> */
final class CustomerAuthProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerAuthProvider::class);
    }

    public function findByProviderUser(string $provider, string $providerUserId): ?CustomerAuthProvider
    {
        return $this->createQueryBuilder('cap')
            ->andWhere('cap.provider = :provider')
            ->andWhere('cap.providerUserId = :providerUserId')
            ->setParameter('provider', $provider)
            ->setParameter('providerUserId', $providerUserId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
