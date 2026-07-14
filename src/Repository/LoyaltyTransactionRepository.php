<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\CustomerLoyalty;
use App\Entity\LoyaltyReward;
use App\Entity\LoyaltyTransaction;
use App\Entity\Order;
use App\Enum\LoyaltyTransactionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<LoyaltyTransaction> */
final class LoyaltyTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyTransaction::class);
    }

    /** @return list<LoyaltyTransaction> */
    public function findByCustomerLoyalty(CustomerLoyalty $account, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.customerLoyalty = :account')
            ->setParameter('account', $account)
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Unexpired Earn batches with remaining points, oldest first (FIFO consumption).
     *
     * @return list<LoyaltyTransaction>
     */
    public function findConsumableEarnBatches(CustomerLoyalty $account): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('t')
            ->andWhere('t.customerLoyalty = :account')
            ->andWhere('t.type = :type')
            ->andWhere('t.remainingPoints > 0')
            ->andWhere('t.expiresAt IS NULL OR t.expiresAt > :now')
            ->setParameter('account', $account)
            ->setParameter('type', LoyaltyTransactionType::Earn)
            ->setParameter('now', $now)
            ->orderBy('t.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyTransaction> */
    public function findEarnBatchesForOrder(Order $order): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.order = :order')
            ->andWhere('t.type = :type')
            ->setParameter('order', $order)
            ->setParameter('type', LoyaltyTransactionType::Earn)
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyTransaction> */
    public function findRedeemBatchesForOrder(Order $order): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.order = :order')
            ->andWhere('t.type = :type')
            ->setParameter('order', $order)
            ->setParameter('type', LoyaltyTransactionType::Redeem)
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyTransaction> */
    public function findExpiredUnprocessed(\DateTimeImmutable $asOf, int $limit = 500): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.type = :type')
            ->andWhere('t.remainingPoints > 0')
            ->andWhere('t.expiresAt IS NOT NULL')
            ->andWhere('t.expiresAt <= :asOf')
            ->setParameter('type', LoyaltyTransactionType::Earn)
            ->setParameter('asOf', $asOf)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyTransaction> */
    public function findExpiringSoon(CustomerLoyalty $account, int $withinDays = 30): array
    {
        $now = new \DateTimeImmutable();
        $until = $now->modify(sprintf('+%d days', $withinDays));

        return $this->createQueryBuilder('t')
            ->andWhere('t.customerLoyalty = :account')
            ->andWhere('t.type = :type')
            ->andWhere('t.remainingPoints > 0')
            ->andWhere('t.expiresAt IS NOT NULL')
            ->andWhere('t.expiresAt > :now')
            ->andWhere('t.expiresAt <= :until')
            ->setParameter('account', $account)
            ->setParameter('type', LoyaltyTransactionType::Earn)
            ->setParameter('now', $now)
            ->setParameter('until', $until)
            ->orderBy('t.expiresAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function sumPointsByType(Boutique $boutique, LoyaltyTransactionType $type, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(ABS(t.points)), 0)')
            ->andWhere('t.boutique = :boutique')
            ->andWhere('t.type = :type')
            ->setParameter('boutique', $boutique)
            ->setParameter('type', $type);

        $this->applyDateRange($qb, $from, $to);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function sumDiscountCents(Boutique $boutique, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COALESCE(SUM(t.discountCents), 0)')
            ->andWhere('t.boutique = :boutique')
            ->andWhere('t.type = :type')
            ->setParameter('boutique', $boutique)
            ->setParameter('type', LoyaltyTransactionType::Redeem);

        $this->applyDateRange($qb, $from, $to);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countRewardsRedeemed(Boutique $boutique, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.boutique = :boutique')
            ->andWhere('t.type = :type')
            ->andWhere('t.reward IS NOT NULL')
            ->setParameter('boutique', $boutique)
            ->setParameter('type', LoyaltyTransactionType::Redeem);

        $this->applyDateRange($qb, $from, $to);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countRewardUsage(LoyaltyReward $reward): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.reward = :reward')
            ->andWhere('t.type = :type')
            ->setParameter('reward', $reward)
            ->setParameter('type', LoyaltyTransactionType::Redeem)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countRewardUsageByCustomer(LoyaltyReward $reward, CustomerLoyalty $account): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.reward = :reward')
            ->andWhere('t.customerLoyalty = :account')
            ->andWhere('t.type = :type')
            ->setParameter('reward', $reward)
            ->setParameter('account', $account)
            ->setParameter('type', LoyaltyTransactionType::Redeem)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function applyDateRange(\Doctrine\ORM\QueryBuilder $qb, ?\DateTimeImmutable $from, ?\DateTimeImmutable $to): void
    {
        if (null !== $from) {
            $qb->andWhere('t.createdAt >= :from')->setParameter('from', $from);
        }
        if (null !== $to) {
            $qb->andWhere('t.createdAt <= :to')->setParameter('to', $to);
        }
    }
}
