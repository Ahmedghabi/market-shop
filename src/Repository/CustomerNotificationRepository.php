<?php

namespace App\Repository;

use App\Entity\CustomerNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CustomerNotification> */
final class CustomerNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerNotification::class);
    }

    /** @return list<CustomerNotification> */
    public function findUnreadByCustomer(string $customerId, int $limit = 50): array
    {
        return $this->createQueryBuilder('cn')
            ->andWhere('cn.customer = :customerId')
            ->andWhere('cn.isRead = 0')
            ->orderBy('cn.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<CustomerNotification> */
    public function findByCustomer(string $customerId, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('cn')
            ->andWhere('cn.customer = :customerId')
            ->orderBy('cn.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByCustomer(string $customerId): int
    {
        return (int) $this->createQueryBuilder('cn')
            ->select('COUNT(cn.id)')
            ->andWhere('cn.customer = :customerId')
            ->andWhere('cn.isRead = 0')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
