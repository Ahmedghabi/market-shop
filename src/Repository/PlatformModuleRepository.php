<?php

namespace App\Repository;

use App\Entity\PlatformModule;
use App\Entity\SubscriptionPlanModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PlatformModule> */
final class PlatformModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlatformModule::class);
    }

    public function findOneByModule(SubscriptionPlanModule $module): ?PlatformModule
    {
        return $this->findOneBy(['module' => $module]);
    }

    /** @return PlatformModule[] */
    public function findEnabled(): array
    {
        return $this->findBy(['isEnabled' => true]);
    }
}
