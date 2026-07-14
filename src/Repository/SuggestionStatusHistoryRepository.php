<?php

namespace App\Repository;

use App\Entity\Suggestion;
use App\Entity\SuggestionStatusHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SuggestionStatusHistory> */
final class SuggestionStatusHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuggestionStatusHistory::class);
    }

    /** @return list<SuggestionStatusHistory> */
    public function findBySuggestion(Suggestion $suggestion): array
    {
        return $this->findBy(['suggestion' => $suggestion], ['createdAt' => 'ASC']);
    }
}
