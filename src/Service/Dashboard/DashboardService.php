<?php

namespace App\Service\Dashboard;

use App\Entity\Boutique;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductStock;
use App\Entity\Subscription;
use App\Entity\UserShop;
use App\Enum\BoutiqueStatus;
use App\Enum\OrderStatus;
use App\Enum\ProductStatus;
use App\Enum\SubscriptionStatus;
use App\Repository\BoutiqueRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DashboardService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BoutiqueRepository $boutiques,
        private ReviewRepository $reviews,
        private DashboardCacheService $cache,
    ) {
    }

    /** @return array<string, mixed> */
    public function platformOverview(): array
    {
        return $this->cache->platform(function (): array {
            $today = new \DateTimeImmutable('today');
            $monthStart = new \DateTimeImmutable('first day of this month 00:00:00');
            $lastMonthStart = $monthStart->modify('-1 month');
            $now = new \DateTimeImmutable();

            $totalBoutiques = $this->count(Boutique::class);
            $activeBoutiques = $this->count(Boutique::class, ['status' => BoutiqueStatus::Active]);
            $pendingBoutiques = $this->count(Boutique::class, ['status' => BoutiqueStatus::Pending]);
            $newBoutiques = $this->countSince(Boutique::class, 'createdAt', $monthStart);

            $totalCustomers = $this->count(Customer::class);
            $totalProducts = $this->count(Product::class, ['status' => ProductStatus::Active]);
            $totalOrders = $this->count(Order::class);
            $ordersToday = $this->countSince(Order::class, 'createdAt', $today);

            $totalAdmins = $this->countByField(UserShop::class, 'role', 'ROLE_BOUTIQUE_ADMIN');
            $totalEmployees = $this->countByField(UserShop::class, 'role', 'ROLE_CAISSIER');

            $platformRevenue = $this->sumOrders(null);
            $monthRevenue = $this->sumOrders($monthStart);
            $lastMonthRevenue = $this->sumOrders($lastMonthStart, $monthStart);
            $growth = $lastMonthRevenue > 0 ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2) : null;

            $activeSubscriptions = $this->count(Subscription::class, ['status' => SubscriptionStatus::Active]);
            $expiredSubscriptions = $this->count(Subscription::class, ['status' => SubscriptionStatus::Expired]);
            $expiringSoon = $this->countSubscriptionsExpiringBefore($now->modify('+7 days'));

            return [
                'kpis' => [
                    'totalBoutiques' => $totalBoutiques,
                    'activeBoutiques' => $activeBoutiques,
                    'expiredBoutiques' => $expiredSubscriptions,
                    'pendingBoutiques' => $pendingBoutiques,
                    'totalCustomers' => $totalCustomers,
                    'totalBoutiqueAdmins' => $totalAdmins,
                    'totalEmployees' => $totalEmployees,
                    'totalProducts' => $totalProducts,
                    'totalOrders' => $totalOrders,
                    'platformRevenueCents' => $platformRevenue,
                    'subscriptionRevenueCents' => 0,
                    'monthlyGrowthPercent' => $growth,
                    'ordersToday' => $ordersToday,
                    'newBoutiques' => $newBoutiques,
                ],
                'subscriptions' => [
                    'active' => $activeSubscriptions,
                    'expiringSoon' => $expiringSoon,
                    'expired' => $expiredSubscriptions,
                ],
                'topBoutiques' => $this->topBoutiques(),
            ];
        });
    }

    /** @return array<string, mixed> */
    public function boutiqueOverview(string $boutiqueId): array
    {
        return $this->cache->boutique($boutiqueId, function () use ($boutiqueId): array {
            $boutique = $this->boutiques->findBySlugOrId($boutiqueId);
            if (!$boutique) {
                return [];
            }

            $today = new \DateTimeImmutable('today');
            $weekStart = $today->modify('-6 days');
            $monthStart = new \DateTimeImmutable('first day of this month 00:00:00');
            $yearStart = new \DateTimeImmutable('first day of january 00:00:00');

            return [
                'boutiqueId' => (string) $boutique->getId(),
                'kpis' => [
                    'salesTodayCents' => $this->sumOrdersForBoutique($boutique, $today),
                    'salesWeekCents' => $this->sumOrdersForBoutique($boutique, $weekStart),
                    'salesMonthCents' => $this->sumOrdersForBoutique($boutique, $monthStart),
                    'salesYearCents' => $this->sumOrdersForBoutique($boutique, $yearStart),
                    'ordersToday' => $this->countOrdersForBoutiqueSince($boutique, $today),
                    'ordersPending' => $this->countOrdersForBoutiqueStatus($boutique, OrderStatus::Pending),
                    'ordersConfirmed' => $this->countOrdersForBoutiqueStatuses($boutique, [OrderStatus::Paid, OrderStatus::Completed]),
                    'ordersShipped' => $this->countOrdersForBoutiqueStatus($boutique, OrderStatus::Shipped),
                    'ordersDelivered' => $this->countOrdersForBoutiqueStatus($boutique, OrderStatus::Delivered),
                    'ordersCancelled' => $this->countOrdersForBoutiqueStatus($boutique, OrderStatus::Cancelled),
                    'customersTotal' => $this->count(Customer::class, ['boutique' => $boutique]),
                    'customersNew' => 0,
                    'productsActive' => $this->count(Product::class, ['boutique' => $boutique, 'status' => ProductStatus::Active]),
                    'productsOutOfStock' => $this->countStockForBoutique($boutique, 'out'),
                    'productsLowStock' => $this->countStockForBoutique($boutique, 'low'),
                    'averageRating' => $this->reviews->getAverageRatingByBoutique($boutique),
                    'reviewsCount' => $this->reviews->countByBoutique($boutique),
                ],
                'topProducts' => $this->topProductsForBoutique($boutique),
            ];
        });
    }

    private function count(string $entity, array $criteria = []): int
    {
        return $this->em->getRepository($entity)->count($criteria);
    }

    private function countByField(string $entity, string $field, string $value): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from($entity, 'e')
            ->andWhere(sprintf('e.%s = :value', $field))
            ->setParameter('value', $value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countSince(string $entity, string $dateField, \DateTimeImmutable $since, array $criteria = []): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from($entity, 'e')
            ->andWhere(sprintf('e.%s >= :since', $dateField))
            ->setParameter('since', $since);

        foreach ($criteria as $field => $value) {
            $qb->andWhere(sprintf('e.%s = :%s', $field, $field))->setParameter($field, $value);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function sumOrders(?\DateTimeImmutable $since = null, ?\DateTimeImmutable $until = null): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COALESCE(SUM(o.totalCents), 0)')
            ->from(Order::class, 'o')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('statuses', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Shipped, OrderStatus::Delivered]);

        if ($since) {
            $qb->andWhere('o.createdAt >= :since')->setParameter('since', $since);
        }
        if ($until) {
            $qb->andWhere('o.createdAt < :until')->setParameter('until', $until);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function sumOrdersForBoutique(Boutique $boutique, \DateTimeImmutable $since): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COALESCE(SUM(o.totalCents), 0)')
            ->from(Order::class, 'o')
            ->andWhere('o.boutique = :boutique')
            ->andWhere('o.status IN (:statuses)')
            ->andWhere('o.createdAt >= :since')
            ->setParameter('boutique', $boutique)
            ->setParameter('statuses', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Shipped, OrderStatus::Delivered])
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countOrdersForBoutiqueSince(Boutique $boutique, \DateTimeImmutable $since): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(Order::class, 'o')
            ->andWhere('o.boutique = :boutique')
            ->andWhere('o.createdAt >= :since')
            ->setParameter('boutique', $boutique)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countOrdersForBoutiqueStatus(Boutique $boutique, OrderStatus $status): int
    {
        return (int) $this->em->getRepository(Order::class)->count(['boutique' => $boutique, 'status' => $status]);
    }

    /** @param list<OrderStatus> $statuses */
    private function countOrdersForBoutiqueStatuses(Boutique $boutique, array $statuses): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(Order::class, 'o')
            ->andWhere('o.boutique = :boutique')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('boutique', $boutique)
            ->setParameter('statuses', $statuses)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function countStockForBoutique(Boutique $boutique, string $mode): int
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(ps.id)')
            ->from(ProductStock::class, 'ps')
            ->innerJoin('ps.product', 'p')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.status = :status')
            ->setParameter('boutique', $boutique)
            ->setParameter('status', ProductStatus::Active);

        if ('out' === $mode) {
            $qb->andWhere('ps.quantity <= 0');
        }
        if ('low' === $mode) {
            $qb->andWhere('ps.quantity > 0')->andWhere('ps.quantity <= ps.lowStockThreshold');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function countSubscriptionsExpiringBefore(\DateTimeImmutable $before): int
    {
        return (int) $this->em->createQueryBuilder()
            ->select('COUNT(s.id)')
            ->from(Subscription::class, 's')
            ->andWhere('s.status = :status')
            ->andWhere('s.endDate IS NOT NULL')
            ->andWhere('s.endDate <= :before')
            ->setParameter('status', SubscriptionStatus::Active)
            ->setParameter('before', $before)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<array<string, mixed>> */
    private function topBoutiques(): array
    {
        $rows = $this->em->createQueryBuilder()
            ->select('IDENTITY(o.boutique) AS id, b.name AS name, COALESCE(SUM(o.totalCents), 0) AS revenue, COUNT(o.id) AS orders')
            ->from(Order::class, 'o')
            ->innerJoin('o.boutique', 'b')
            ->andWhere('o.status IN (:statuses)')
            ->setParameter('statuses', [OrderStatus::Paid, OrderStatus::Completed, OrderStatus::Shipped, OrderStatus::Delivered])
            ->groupBy('b.id')
            ->orderBy('revenue', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getArrayResult();

        return array_map(fn (array $row) => [
            'id' => (string) $row['id'],
            'name' => (string) $row['name'],
            'revenueCents' => (int) $row['revenue'],
            'orders' => (int) $row['orders'],
        ], $rows);
    }

    /** @return list<array<string, mixed>> */
    private function topProductsForBoutique(Boutique $boutique): array
    {
        $rows = $this->em->createQueryBuilder()
            ->select('p.id AS id, p.name AS name, COUNT(oi.id) AS sales, COALESCE(SUM(oi.totalCents), 0) AS revenue')
            ->from(Product::class, 'p')
            ->leftJoin(OrderItem::class, 'oi', 'WITH', 'oi.product = p')
            ->andWhere('p.boutique = :boutique')
            ->groupBy('p.id')
            ->orderBy('sales', 'DESC')
            ->addOrderBy('revenue', 'DESC')
            ->setParameter('boutique', $boutique)
            ->setMaxResults(5)
            ->getQuery()
            ->getArrayResult();

        return array_map(fn (array $row) => [
            'id' => (string) $row['id'],
            'name' => (string) $row['name'],
            'salesCount' => (int) $row['sales'],
            'revenueCents' => (int) $row['revenue'],
        ], $rows);
    }
}
