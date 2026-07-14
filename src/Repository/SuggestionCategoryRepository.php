<?php

namespace App\Repository;

use App\Entity\SuggestionCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SuggestionCategory> */
final class SuggestionCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuggestionCategory::class);
    }

    /** @return list<SuggestionCategory> */
    public function findActiveOrdered(): array
    {
        return $this->findBy(['isActive' => true], ['position' => 'ASC', 'name' => 'ASC']);
    }

    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('category')
            ->select('COUNT(category.id)')
            ->andWhere('category.slug = :slug')
            ->setParameter('slug', $slug);
        if (null !== $excludeId) {
            $qb->andWhere('category.id != :excludeId')->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
