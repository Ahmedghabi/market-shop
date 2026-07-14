<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Suggestion;
use App\Entity\User;
use App\Enum\SuggestionStatus;
use App\Enum\SuggestionVisibility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Suggestion> */
final class SuggestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Suggestion::class);
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    public function findForBoutique(Boutique $boutique, array $filters = [], int $limit = 30, int $offset = 0): array
    {
        $qb = $this->createFilteredQuery($filters)
            ->andWhere('suggestion.boutique = :boutique')
            ->andWhere('suggestion.tenantId = :tenantId')
            ->setParameter('boutique', $boutique)
            ->setParameter('tenantId', $boutique->getId());

        return $this->executeList($qb, $filters, $limit, $offset);
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    public function findForUser(Boutique $boutique, User $user, array $filters = [], int $limit = 30, int $offset = 0): array
    {
        $qb = $this->createFilteredQuery($filters)
            ->andWhere('suggestion.boutique = :boutique')
            ->andWhere('(suggestion.createdBy = :user OR (suggestion.visibility = :public AND suggestion.isPublished = true))')
            ->andWhere('suggestion.tenantId = :tenantId')
            ->setParameter('boutique', $boutique)
            ->setParameter('tenantId', $boutique->getId())
            ->setParameter('user', $user)
            ->setParameter('public', SuggestionVisibility::PUBLIC)
            ->andWhere('suggestion.status != :draft')
            ->setParameter('draft', SuggestionStatus::DRAFT);

        return $this->executeList($qb, $filters, $limit, $offset);
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    public function findPublic(Boutique $boutique, array $filters = [], int $limit = 30, int $offset = 0): array
    {
        $qb = $this->createFilteredQuery($filters)
            ->andWhere('suggestion.boutique = :boutique')
            ->andWhere('suggestion.isPublished = true')
            ->andWhere('suggestion.visibility = :visibility')
            ->andWhere('suggestion.status NOT IN (:hiddenStatuses)')
            ->andWhere('suggestion.tenantId = :tenantId')
            ->setParameter('boutique', $boutique)
            ->setParameter('tenantId', $boutique->getId())
            ->setParameter('visibility', SuggestionVisibility::PUBLIC)
            ->setParameter('hiddenStatuses', [SuggestionStatus::DRAFT, SuggestionStatus::ARCHIVED, SuggestionStatus::REJECTED]);

        return $this->executeList($qb, $filters, $limit, $offset);
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    public function findPublicAll(array $filters = [], int $limit = 30, int $offset = 0): array
    {
        $qb = $this->createFilteredQuery($filters)
            ->andWhere('suggestion.isPublished = true')
            ->andWhere('suggestion.visibility = :visibility')
            ->andWhere('suggestion.status NOT IN (:hiddenStatuses)')
            ->setParameter('visibility', SuggestionVisibility::PUBLIC)
            ->setParameter('hiddenStatuses', [SuggestionStatus::DRAFT, SuggestionStatus::ARCHIVED, SuggestionStatus::REJECTED]);

        return $this->executeList($qb, $filters, $limit, $offset);
    }

    /** @param array<string, mixed> $filters */
    public function countForBoutique(Boutique $boutique, array $filters = [], bool $public = false, ?User $user = null): int
    {
        $qb = $this->createFilteredQuery($filters)->select('COUNT(suggestion.id)')->andWhere('suggestion.boutique = :boutique')->andWhere('suggestion.tenantId = :tenantId')->setParameter('boutique', $boutique)->setParameter('tenantId', $boutique->getId());
        if ($public) {
            $qb->andWhere('suggestion.isPublished = true')->andWhere('suggestion.visibility = :visibility')->setParameter('visibility', SuggestionVisibility::PUBLIC);
        } elseif (null !== $user) {
            $qb->andWhere('(suggestion.createdBy = :user OR (suggestion.visibility = :public AND suggestion.isPublished = true))')
                ->setParameter('user', $user)
                ->setParameter('public', SuggestionVisibility::PUBLIC);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findOneForBoutique(string $id, Boutique $boutique): ?Suggestion
    {
        return $this->createQueryBuilder('suggestion')
            ->andWhere('suggestion.id = :id')->andWhere('suggestion.boutique = :boutique')
            ->andWhere('suggestion.tenantId = :tenantId')
            ->setParameter('id', $id)->setParameter('boutique', $boutique)->setParameter('tenantId', $boutique->getId())
            ->getQuery()->getOneOrNullResult();
    }

    public function findOnePublic(string $id, Boutique $boutique): ?Suggestion
    {
        return $this->createQueryBuilder('suggestion')
            ->andWhere('suggestion.id = :id')->andWhere('suggestion.boutique = :boutique')
            ->andWhere('suggestion.isPublished = true')->andWhere('suggestion.visibility = :visibility')
            ->andWhere('suggestion.status NOT IN (:hiddenStatuses)')
            ->andWhere('suggestion.tenantId = :tenantId')
            ->setParameter('id', $id)->setParameter('boutique', $boutique)->setParameter('tenantId', $boutique->getId())
            ->setParameter('visibility', SuggestionVisibility::PUBLIC)
            ->setParameter('hiddenStatuses', [SuggestionStatus::DRAFT, SuggestionStatus::ARCHIVED, SuggestionStatus::REJECTED])
            ->getQuery()->getOneOrNullResult();
    }

    public function findOnePublicAny(string $id): ?Suggestion
    {
        return $this->createQueryBuilder('suggestion')
            ->andWhere('suggestion.id = :id')
            ->andWhere('suggestion.isPublished = true')
            ->andWhere('suggestion.visibility = :visibility')
            ->andWhere('suggestion.status NOT IN (:hiddenStatuses)')
            ->setParameter('id', $id)
            ->setParameter('visibility', SuggestionVisibility::PUBLIC)
            ->setParameter('hiddenStatuses', [SuggestionStatus::DRAFT, SuggestionStatus::ARCHIVED, SuggestionStatus::REJECTED])
            ->getQuery()->getOneOrNullResult();
    }

    /** @param array<string, mixed> $filters */
    public function findForExport(?Boutique $boutique, array $filters = []): array
    {
        $qb = $this->createFilteredQuery($filters)->orderBy('suggestion.createdAt', 'DESC');
        if (null !== $boutique) {
            $qb->andWhere('suggestion.boutique = :boutique')->andWhere('suggestion.tenantId = :tenantId')->setParameter('boutique', $boutique)->setParameter('tenantId', $boutique->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    public function findForSuperAdmin(array $filters = [], int $limit = 30, int $offset = 0): array
    {
        return $this->executeList($this->createFilteredQuery($filters), $filters, $limit, $offset);
    }

    /** @param array<string, mixed> $filters */
    private function createFilteredQuery(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('suggestion');
        if (!empty($filters['search'])) {
            $qb->andWhere('(LOWER(suggestion.title) LIKE :search OR LOWER(suggestion.description) LIKE :search)')
                ->setParameter('search', '%'.mb_strtolower((string) $filters['search']).'%');
        }
        if (!empty($filters['category'])) {
            $qb->andWhere('suggestion.category = :category')->setParameter('category', $filters['category']);
        }
        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status']) ? $filters['status'] : [(string) $filters['status']];
            $qb->andWhere('suggestion.status IN (:statuses)')->setParameter('statuses', array_map(static fn (SuggestionStatus|string $status): SuggestionStatus => $status instanceof SuggestionStatus ? $status : SuggestionStatus::from($status), $statuses));
        }
        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            if (!empty($filters[$key])) {
                $field = 'from' === $key ? 'createdAt' : 'createdAt';
                $qb->andWhere(sprintf('suggestion.%s %s :%s', $field, $operator, $key))->setParameter($key, $filters[$key]);
            }
        }

        return $qb;
    }

    /** @param array<string, mixed> $filters @return list<Suggestion> */
    private function executeList(QueryBuilder $qb, array $filters, int $limit, int $offset): array
    {
        $sort = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['suggestion.createdAt', 'ASC'],
            'title' => ['suggestion.title', 'ASC'],
            'updated' => ['suggestion.updatedAt', 'DESC'],
            default => ['suggestion.createdAt', 'DESC'],
        };

        return $qb->orderBy($sort[0], $sort[1])->setMaxResults(min(100, max(1, $limit)))->setFirstResult(max(0, $offset))->getQuery()->getResult();
    }
}
