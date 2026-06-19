<?php

namespace App\Repository;

use App\Entity\Refund;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Refund> */
final class RefundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Refund::class);
    }

    /** @return list<Refund> */
    public function findByBoutique(string $boutiqueId, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.boutique = :boutiqueId')
            ->setParameter('boutiqueId', $boutiqueId)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function nextSequence(string $boutiqueId, int $year): int
    {
        $result = $this->createQueryBuilder('r')
            ->select('COALESCE(MAX(CAST(SUBSTRING_INDEX(r.refundNumber, \'-\', -1) AS UNSIGNED)), 0) + 1')
            ->andWhere('r.boutique = :boutiqueId')
            ->andWhere('r.refundNumber LIKE :prefix')
            ->setParameter('boutiqueId', $boutiqueId)
            ->setParameter('prefix', '%'.$year.'-%')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result;
    }

    public function findOneByOrderAndType(string $orderId, string $type): ?Refund
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.order = :orderId')
            ->andWhere('r.type = :type')
            ->setParameter('orderId', $orderId)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countTotalRefundedForOrder(string $orderId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.totalCents), 0)')
            ->andWhere('r.order = :orderId')
            ->andWhere('r.status = :status')
            ->setParameter('orderId', $orderId)
            ->setParameter('status', 'PROCESSED')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
