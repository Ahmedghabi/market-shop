<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Boutique;
use Symfony\Component\Uid\Uuid;

/** @extends ServiceEntityRepository<Product> */
final class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findBySlugOrId(string $identifier, ?Boutique $boutique = null): ?Product
    {
        if (null === $boutique) {
            return Uuid::isValid($identifier)
                ? $this->find($identifier)
                : null;
        }

        if (Uuid::isValid($identifier)) {
            $product = $this->find($identifier);
            if ($product && (string) $product->getBoutique()->getId() === (string) $boutique->getId()) {
                return $product;
            }

            return null;
        }

        return $this->findOneBy(['boutique' => $boutique, 'slug' => $identifier]);
    }

    /** @return list<Product> */
    public function findSeoIndexedByBoutique(Boutique $boutique): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.deletedAt IS NULL')
            ->andWhere('p.status = :status')
            ->setParameter('boutique', $boutique)
            ->setParameter('status', \App\Enum\ProductStatus::Active)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countSeoIndexedByBoutique(Boutique $boutique): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.boutique = :boutique')
            ->andWhere('p.deletedAt IS NULL')
            ->andWhere('p.status = :status')
            ->setParameter('boutique', $boutique)
            ->setParameter('status', \App\Enum\ProductStatus::Active)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
