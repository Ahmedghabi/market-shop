<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Extension;
use App\Entity\BoutiqueExtension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<BoutiqueExtension> */
final class BoutiqueExtensionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoutiqueExtension::class);
    }

    /** @return BoutiqueExtension[] */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique, 'isActive' => true]);
    }

    public function findOneActiveByBoutiqueAndExtension(Boutique $boutique, Extension $extension): ?BoutiqueExtension
    {
        return $this->findOneBy(['boutique' => $boutique, 'extension' => $extension, 'isActive' => true]);
    }

    /** @return BoutiqueExtension[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['activatedAt' => 'DESC']);
    }

    /** @return BoutiqueExtension[] active grants that have an expiresAt in the past */
    public function findExpiredButStillActive(\DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('be')
            ->andWhere('be.isActive = :active')
            ->andWhere('be.expiresAt IS NOT NULL')
            ->andWhere('be.expiresAt <= :now')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    /** @return BoutiqueExtension[] active grants expiring within the given window, not yet notified */
    public function findExpiringSoon(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('be')
            ->andWhere('be.isActive = :active')
            ->andWhere('be.expiresAt IS NOT NULL')
            ->andWhere('be.expiresAt > :from')
            ->andWhere('be.expiresAt <= :to')
            ->andWhere('be.expiryNotifiedAt IS NULL')
            ->setParameter('active', true)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }
}
