<?php

namespace App\Repository;

use App\Entity\LoyaltyProgram;
use App\Entity\LoyaltyRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<LoyaltyRule> */
final class LoyaltyRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyRule::class);
    }

    /** @return list<LoyaltyRule> */
    public function findByProgram(LoyaltyProgram $program): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.program = :program')
            ->setParameter('program', $program)
            ->orderBy('r.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyRule> */
    public function findActiveByProgram(LoyaltyProgram $program): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.program = :program')
            ->andWhere('r.isActive = :active')
            ->setParameter('program', $program)
            ->setParameter('active', true)
            ->orderBy('r.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
