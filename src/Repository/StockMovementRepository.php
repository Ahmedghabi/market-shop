<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Product;
use App\Entity\StockMovement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<StockMovement> */
final class StockMovementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockMovement::class);
    }

    /** @return StockMovement[] */
    public function findByProduct(Product $product): array
    {
        return $this->findBy(['product' => $product], ['createdAt' => 'DESC']);
    }

    /** @return StockMovement[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['createdAt' => 'DESC']);
    }
}
