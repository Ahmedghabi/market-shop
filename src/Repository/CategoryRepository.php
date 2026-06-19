<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Category> */
final class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /** @return Category[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique, 'deletedAt' => null], ['name' => 'ASC']);
    }

    /** @return Category[] */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'isActive' => true, 'deletedAt' => null],
            ['name' => 'ASC'],
        );
    }

    /** @return Category[] */
    public function findRootByBoutique(Boutique $boutique): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'parent' => null, 'deletedAt' => null],
            ['name' => 'ASC'],
        );
    }

    /** @return Category[] */
    public function findByBoutiqueAndParent(Boutique $boutique, ?Category $parent = null): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'parent' => $parent, 'deletedAt' => null],
            ['name' => 'ASC'],
        );
    }

    public function slugExistsInBoutique(string $slug, Boutique $boutique, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.boutique = :boutique')
            ->andWhere('c.slug = :slug')
            ->setParameter('boutique', $boutique)
            ->setParameter('slug', $slug);

        if (null !== $excludeId) {
            $qb->andWhere('c.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /** @return list<Category> */
    public function findSeoIndexedByBoutique(Boutique $boutique): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'isActive' => true, 'showCategoryPage' => true, 'deletedAt' => null],
            ['name' => 'ASC'],
        );
    }

    public function countSeoIndexedByBoutique(Boutique $boutique): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.boutique = :boutique')
            ->andWhere('c.isActive = true')
            ->andWhere('c.showCategoryPage = true')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('boutique', $boutique)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
