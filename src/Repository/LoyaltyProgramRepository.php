<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\LoyaltyProgram;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<LoyaltyProgram> */
final class LoyaltyProgramRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoyaltyProgram::class);
    }

    public function findOneByBoutique(Boutique $boutique): ?LoyaltyProgram
    {
        return $this->findOneBy(['boutique' => $boutique]);
    }
}
