<?php

namespace App\Repository;

use App\Entity\LoyaltyProgram;
use App\Entity\LoyaltyReward;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<LoyaltyReward> */
final class LoyaltyRewardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyReward::class);
    }

    /** @return list<LoyaltyReward> */
    public function findByProgram(LoyaltyProgram $program): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.program = :program')
            ->setParameter('program', $program)
            ->orderBy('r.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<LoyaltyReward> */
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
