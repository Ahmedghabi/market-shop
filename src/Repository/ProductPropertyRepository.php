<?php

namespace App\Repository;

use App\Entity\ProductProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ProductProperty> */
final class ProductPropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductProperty::class);
    }

    /** @return ProductProperty[] */
    public function findByProduct(Product $product): array
    {
        return $this->findBy(['product' => $product]);
    }
}
