<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<OrderItem> */
final class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    public function hasPurchasedProduct(User $user, Product $product): bool
    {
        return 0 < (int) $this->createQueryBuilder('oi')
            ->select('COUNT(oi.id)')
            ->innerJoin('oi.order', 'o')
            ->innerJoin('o.customer', 'customer')
            ->andWhere('customer.user = :user')
            ->andWhere('oi.product = :product')
            ->setParameter('user', $user)
            ->setParameter('product', $product)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
