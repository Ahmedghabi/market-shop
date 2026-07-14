<?php

namespace App\Service\Subscription;

use App\Entity\Boutique;
use App\Entity\Category;
use App\Entity\Product;
use App\Enum\ProductStatus;
use Doctrine\ORM\EntityManagerInterface;

/**
 * When a boutique's plan changes or expires, the quota may drop below current usage.
 * This reconciler deactivates the oldest active items until usage fits within the new limit.
 */
final readonly class SubscriptionQuotaReconciler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SubscriptionManager $subscriptionManager,
    ) {
    }

    /**
     * Deactivates products/categories that exceed the current plan's quota.
     * Returns total number of items deactivated.
     */
    public function reconcile(Boutique $boutique): int
    {
        $total = 0;
        $total += $this->reconcileProducts($boutique);
        $total += $this->reconcileCategories($boutique);

        return $total;
    }

    /** Deactivates all storefront content when a boutique has no active plan. */
    public function reconcileExpired(Boutique $boutique): int
    {
        $products = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.status = :status')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('boutique', $boutique->getId())
            ->setParameter('status', ProductStatus::Active)
            ->getQuery()
            ->getResult();

        $categories = $this->em->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->andWhere('c.boutique = :boutique')
            ->andWhere('c.isActive = :active')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('boutique', $boutique->getId())
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();

        foreach ($products as $product) {
            $product->setStatus(ProductStatus::Inactive);
        }
        foreach ($categories as $category) {
            $category->setIsActive(false);
        }

        $count = count($products) + count($categories);
        if ($count > 0) {
            $this->em->flush();
        }

        return $count;
    }

    private function reconcileProducts(Boutique $boutique): int
    {
        $max = $this->subscriptionManager->getLimit('max_products', $boutique);
        if (null === $max) {
            return 0;
        }

        $active = $this->subscriptionManager->getUsage('max_products', $boutique);
        $excess = $active - $max;
        if ($excess <= 0) {
            return 0;
        }

        $oldest = $this->findOldestActiveProducts($boutique, $excess);
        foreach ($oldest as $product) {
            $product->setStatus(ProductStatus::Inactive);
        }

        if ([] !== $oldest) {
            $this->em->flush();
        }

        return \count($oldest);
    }

    private function reconcileCategories(Boutique $boutique): int
    {
        $max = $this->subscriptionManager->getLimit('max_categories', $boutique);
        if (null === $max) {
            return 0;
        }

        $active = $this->subscriptionManager->getUsage('max_categories', $boutique);
        $excess = $active - $max;
        if ($excess <= 0) {
            return 0;
        }

        $oldest = $this->findOldestActiveCategories($boutique, $excess);
        foreach ($oldest as $category) {
            $category->setIsActive(false);
        }

        if ([] !== $oldest) {
            $this->em->flush();
        }

        return \count($oldest);
    }

    /** @return list<Product> */
    private function findOldestActiveProducts(Boutique $boutique, int $limit): array
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.status = :status')
            ->andWhere('p.deletedAt IS NULL')
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('boutique', $boutique->getId())
            ->setParameter('status', ProductStatus::Active)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Category> */
    private function findOldestActiveCategories(Boutique $boutique, int $limit): array
    {
        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->andWhere('c.boutique = :boutique')
            ->andWhere('c.deletedAt IS NULL')
            ->andWhere('c.isActive = :active')
            ->orderBy('c.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->setParameter('boutique', $boutique->getId())
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
