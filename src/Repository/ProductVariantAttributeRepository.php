<?php

namespace App\Repository;

use App\Entity\ProductVariant;
use App\Entity\ProductVariantAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ProductVariantAttribute> */
final class ProductVariantAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVariantAttribute::class);
    }

    /** @return ProductVariantAttribute[] */
    public function findByVariant(ProductVariant $variant): array
    {
        return $this->findBy(['variant' => $variant]);
    }
}
