<?php

namespace App\Repository;

use App\Entity\SubscriptionModule;
use App\Entity\SubscriptionPlan;
use App\Entity\SubscriptionPlanModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<SubscriptionModule> */
final class SubscriptionModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionModule::class);
    }

    /** @return SubscriptionModule[] */
    public function findByPlan(SubscriptionPlan $plan): array
    {
        return $this->findBy(['plan' => $plan], ['createdAt' => 'ASC']);
    }

    public function findOneByPlanAndModule(SubscriptionPlan $plan, SubscriptionPlanModule $module): ?SubscriptionModule
    {
        return $this->findOneBy(['plan' => $plan, 'module' => $module]);
    }

    /** @return SubscriptionModule[] */
    public function findAllowedByPlan(SubscriptionPlan $plan): array
    {
        return $this->findBy(['plan' => $plan, 'isAllowed' => true]);
    }

    /** @return string[] */
    public function findAllowedModuleCodes(SubscriptionPlan $plan): array
    {
        $qb = $this->createQueryBuilder('sm')
            ->select('m.code')
            ->join('sm.module', 'm')
            ->andWhere('sm.plan = :plan')
            ->andWhere('sm.isAllowed = :allowed')
            ->setParameter('plan', $plan)
            ->setParameter('allowed', true);

        return array_map('current', $qb->getQuery()->getScalarResult());
    }
}
