<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\BoutiqueDeliveryAccount;
use App\Entity\DeliveryCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BoutiqueDeliveryAccount> */
final class BoutiqueDeliveryAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoutiqueDeliveryAccount::class);
    }

    /** @return list<BoutiqueDeliveryAccount> */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.boutique = :boutique')
            ->setParameter('boutique', $boutique)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<BoutiqueDeliveryAccount> */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.boutique = :boutique')
            ->andWhere('a.isActive = :active')
            ->andWhere('a.isVerified = :verified')
            ->setParameter('boutique', $boutique)
            ->setParameter('active', true)
            ->setParameter('verified', true)
            ->getQuery()
            ->getResult();
    }

    public function findOneByBoutiqueAndCompany(Boutique $boutique, DeliveryCompany $company): ?BoutiqueDeliveryAccount
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.boutique = :boutique')
            ->andWhere('a.deliveryCompany = :company')
            ->setParameter('boutique', $boutique)
            ->setParameter('company', $company)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDefaultForBoutique(Boutique $boutique): ?BoutiqueDeliveryAccount
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.boutique = :boutique')
            ->andWhere('a.isDefault = :default')
            ->andWhere('a.isActive = :active')
            ->setParameter('boutique', $boutique)
            ->setParameter('default', true)
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function clearDefaultForBoutique(Boutique $boutique): void
    {
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.isDefault', ':false')
            ->andWhere('a.boutique = :boutique')
            ->setParameter('false', false)
            ->setParameter('boutique', $boutique)
            ->getQuery()
            ->execute();
    }
}
