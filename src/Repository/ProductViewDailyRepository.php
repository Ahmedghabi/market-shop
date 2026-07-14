<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Product;
use App\Entity\ProductViewDaily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ProductViewDaily> */
final class ProductViewDailyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductViewDaily::class);
    }

    public function incrementForProduct(Product $product, \DateTimeImmutable $date): void
    {
        $this->getEntityManager()->getConnection()->executeStatement(
            <<<'SQL'
            INSERT INTO product_view_daily (id, product_id, view_date, views_count)
            VALUES (:id, :product, :view_date, 1)
            ON CONFLICT (product_id, view_date)
            DO UPDATE SET views_count = product_view_daily.views_count + 1
            SQL,
            [
                'id' => $this->newId(),
                'product' => $product->getId()->toRfc4122(),
                'view_date' => $date->format('Y-m-d'),
            ],
        );
    }

    /** @return list<array{date: string, views: int}> */
    public function findDailyTotals(?Boutique $boutique, \DateTimeImmutable $since): array
    {
        $queryBuilder = $this->createQueryBuilder('daily')
            ->select('daily.viewDate AS date, SUM(daily.viewsCount) AS views')
            ->join('daily.product', 'product')
            ->andWhere('daily.viewDate >= :since')
            ->andWhere('product.deletedAt IS NULL')
            ->setParameter('since', $since->setTime(0, 0))
            ->groupBy('daily.viewDate')
            ->orderBy('daily.viewDate', 'ASC');

        if ($boutique instanceof Boutique) {
            $queryBuilder
                ->andWhere('product.boutique = :boutique')
                ->setParameter('boutique', $boutique);
        }

        return array_map(
            static fn (array $row): array => [
                'date' => self::formatDate($row['date']),
                'views' => (int) $row['views'],
            ],
            $queryBuilder->getQuery()->getArrayResult(),
        );
    }

    /** @return list<array{date: string, productId: string, productName: string, views: int}> */
    public function findDailyProductStats(?Boutique $boutique, \DateTimeImmutable $since): array
    {
        $queryBuilder = $this->createQueryBuilder('daily')
            ->select(
                'daily.viewDate AS date',
                'product.id AS productId',
                'product.name AS productName',
                'SUM(daily.viewsCount) AS views',
            )
            ->join('daily.product', 'product')
            ->andWhere('daily.viewDate >= :since')
            ->andWhere('product.deletedAt IS NULL')
            ->setParameter('since', $since->setTime(0, 0))
            ->groupBy('daily.viewDate')
            ->addGroupBy('product.id')
            ->addGroupBy('product.name')
            ->orderBy('daily.viewDate', 'ASC')
            ->addOrderBy('views', 'DESC');

        if ($boutique instanceof Boutique) {
            $queryBuilder
                ->andWhere('product.boutique = :boutique')
                ->setParameter('boutique', $boutique);
        }

        return array_map(
            static fn (array $row): array => [
                'date' => self::formatDate($row['date']),
                'productId' => (string) $row['productId'],
                'productName' => (string) $row['productName'],
                'views' => (int) $row['views'],
            ],
            $queryBuilder->getQuery()->getArrayResult(),
        );
    }

    /** @return list<array{date: string, boutiqueId: string, boutiqueName: string, views: int}> */
    public function findDailyBoutiqueStats(?Boutique $boutique, \DateTimeImmutable $since): array
    {
        $queryBuilder = $this->createQueryBuilder('daily')
            ->select(
                'daily.viewDate AS date',
                'boutique.id AS boutiqueId',
                'boutique.name AS boutiqueName',
                'SUM(daily.viewsCount) AS views',
            )
            ->join('daily.product', 'product')
            ->join('product.boutique', 'boutique')
            ->andWhere('daily.viewDate >= :since')
            ->andWhere('product.deletedAt IS NULL')
            ->setParameter('since', $since->setTime(0, 0))
            ->groupBy('daily.viewDate')
            ->addGroupBy('boutique.id')
            ->addGroupBy('boutique.name')
            ->orderBy('daily.viewDate', 'ASC')
            ->addOrderBy('views', 'DESC');

        if ($boutique instanceof Boutique) {
            $queryBuilder
                ->andWhere('boutique = :boutique')
                ->setParameter('boutique', $boutique);
        }

        $rows = $queryBuilder->getQuery()->getArrayResult();

        return array_map(
            static fn (array $row): array => [
                'date' => self::formatDate($row['date']),
                'boutiqueId' => (string) $row['boutiqueId'],
                'boutiqueName' => (string) $row['boutiqueName'],
                'views' => (int) $row['views'],
            ],
            $rows,
        );
    }

    private function newId(): string
    {
        return \Symfony\Component\Uid\Uuid::v7()->toRfc4122();
    }

    private static function formatDate(mixed $date): string
    {
        return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;
    }
}
