<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductVariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ProductVariant> */
final class ProductVariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariant::class);
    }

    /** @return ProductVariant[] */
    public function findByProduct(Product $product): array
    {
        return $this->findBy(['product' => $product], ['createdAt' => 'ASC']);
    }

    public function findDefaultByProduct(Product $product): ?ProductVariant
    {
        return $this->findOneBy(['product' => $product, 'isDefault' => true]);
    }
}
