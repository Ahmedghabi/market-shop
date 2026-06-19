<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<AuditLog> */
final class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    /** @return list<AuditLog> */
    public function findByBoutique(?string $boutiqueId, int $limit = 100, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('a');

        if (null !== $boutiqueId) {
            $qb->andWhere('a.boutique = :boutiqueId')
                ->setParameter('boutiqueId', $boutiqueId);
        }

        return $qb->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
