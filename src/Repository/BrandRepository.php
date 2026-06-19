<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Brand> */
final class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    /** @return Brand[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique, 'deletedAt' => null], ['name' => 'ASC']);
    }
}
