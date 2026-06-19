<?php

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Announcement> */
final class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    /** @return array<Announcement> */
    public function findActiveByBoutique(string $boutiqueId): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('a')
            ->andWhere('a.boutique = :boutiqueId')
            ->andWhere('a.active = :active')
            ->andWhere('a.startsAt IS NULL OR a.startsAt <= :now')
            ->andWhere('a.endsAt IS NULL OR a.endsAt >= :now')
            ->setParameter('boutiqueId', $boutiqueId)
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('a.priority', 'ASC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return array<Announcement> */
    public function findVisibleForStorefront(string $boutiqueId): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('a')
            ->andWhere('(a.boutique = :boutiqueId OR a.isGlobal = :isGlobal)')
            ->andWhere('a.active = :active')
            ->andWhere('a.startsAt IS NULL OR a.startsAt <= :now')
            ->andWhere('a.endsAt IS NULL OR a.endsAt >= :now')
            ->setParameter('boutiqueId', $boutiqueId)
            ->setParameter('isGlobal', true)
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('a.priority', 'ASC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
