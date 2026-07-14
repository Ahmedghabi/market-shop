<?php

namespace App\Repository;

use App\Entity\SubscriptionPlanModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionPlanModule> */
final class SubscriptionPlanModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlanModule::class);
    }

    public function findOneByCode(string $code): ?SubscriptionPlanModule
    {
        return $this->findOneBy(['code' => $code]);
    }
}
