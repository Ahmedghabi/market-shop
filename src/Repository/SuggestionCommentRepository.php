<?php

namespace App\Repository;

use App\Entity\Suggestion;
use App\Entity\SuggestionComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SuggestionComment> */
final class SuggestionCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuggestionComment::class);
    }

    /** @return list<SuggestionComment> */
    public function findBySuggestion(Suggestion $suggestion): array
    {
        return $this->createQueryBuilder('comment')->andWhere('comment.suggestion = :suggestion')->setParameter('suggestion', $suggestion)->orderBy('comment.createdAt', 'ASC')->getQuery()->getResult();
    }

    public function countBySuggestion(Suggestion $suggestion): int
    {
        return (int) $this->createQueryBuilder('comment')->select('COUNT(comment.id)')->andWhere('comment.suggestion = :suggestion')->setParameter('suggestion', $suggestion)->getQuery()->getSingleScalarResult();
    }

    public function findOneForSuggestion(string $id, Suggestion $suggestion): ?SuggestionComment
    {
        return $this->findOneBy(['id' => $id, 'suggestion' => $suggestion]);
    }
}
