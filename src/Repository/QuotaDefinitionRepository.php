<?php

namespace App\Repository;

use App\Entity\QuotaDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<QuotaDefinition> */
final class QuotaDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuotaDefinition::class);
    }

    public function findOneByCode(string $code): ?QuotaDefinition
    {
        return $this->findOneBy(['code' => $code]);
    }

    /** @return QuotaDefinition[] */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true], ['category' => 'ASC', 'name' => 'ASC']);
    }
}
