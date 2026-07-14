<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\User;
use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Order> */
final class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function hasOrdersByUserForBoutique(User $user, Boutique $boutique): bool
    {
        return 0 < (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->innerJoin('o.customer', 'customer')
            ->andWhere('customer.user = :user')
            ->andWhere('o.boutique = :boutique')
            ->setParameter('user', $user)
            ->setParameter('boutique', $boutique)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count of non-cancelled orders placed by this customer for this boutique —
     * used by the loyalty engine for first-purchase/order-count/min-orders rules.
     */
    public function countValidByCustomer(\App\Entity\Customer $customer): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.status != :cancelled')
            ->setParameter('customer', $customer)
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<Order> */
    public function findPaid(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', OrderStatus::Paid)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Order> */
    public function findShippedNotDelivered(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.deliveredAt IS NULL')
            ->setParameter('status', OrderStatus::Shipped)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Order> */
    public function findPendingDeliverySubmission(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.submittedToDelivery = :submitted')
            ->setParameter('status', OrderStatus::Paid)
            ->setParameter('submitted', false)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Order> */
    public function findDeliveryFailedForRetry(int $maxRetries = 5, int $retryIntervalSeconds = 3600): array
    {
        $threshold = new \DateTimeImmutable(sprintf('-%d seconds', $retryIntervalSeconds));

        return $this->createQueryBuilder('o')
            ->andWhere('o.status = :status')
            ->andWhere('o.submittedToDelivery = :submitted')
            ->andWhere('o.deliveryRetryCount < :maxRetries')
            ->andWhere('o.lastRetryAt IS NULL OR o.lastRetryAt < :threshold')
            ->setParameter('status', OrderStatus::Paid)
            ->setParameter('submitted', false)
            ->setParameter('maxRetries', $maxRetries)
            ->setParameter('threshold', $threshold)
            ->orderBy('o.lastRetryAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
