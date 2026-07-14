<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Suggestion;
use App\Entity\SuggestionReaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SuggestionReaction> */
final class SuggestionReactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuggestionReaction::class);
    }

    public function findOneBySuggestionAndUser(Suggestion $suggestion, User $user): ?SuggestionReaction
    {
        return $this->findOneBy(['suggestion' => $suggestion, 'user' => $user]);
    }

    /** @return list<SuggestionReaction> */
    public function findBySuggestion(Suggestion $suggestion): array
    {
        return $this->findBy(['suggestion' => $suggestion], ['createdAt' => 'DESC']);
    }

    /** @return array<string, int> */
    public function countByType(Suggestion $suggestion): array
    {
        $rows = $this->createQueryBuilder('reaction')
            ->select('reaction.type AS type, COUNT(reaction.id) AS total')
            ->andWhere('reaction.suggestion = :suggestion')->setParameter('suggestion', $suggestion)
            ->groupBy('reaction.type')->getQuery()->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $type = $row['type'];
            $key = $type instanceof \BackedEnum ? $type->value : (string) $type;
            $counts[$key] = (int) $row['total'];
        }

        return $counts;
    }

    public function countForBoutique(Boutique $boutique): int
    {
        return (int) $this->createQueryBuilder('reaction')->select('COUNT(reaction.id)')->andWhere('reaction.boutique = :boutique')->setParameter('boutique', $boutique)->getQuery()->getSingleScalarResult();
    }

    public function countBySuggestion(Suggestion $suggestion): int
    {
        return (int) $this->createQueryBuilder('reaction')->select('COUNT(reaction.id)')->andWhere('reaction.suggestion = :suggestion')->setParameter('suggestion', $suggestion)->getQuery()->getSingleScalarResult();
    }
}
