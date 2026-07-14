<?php

namespace App\Repository;

use App\Entity\PlanQuota;
use App\Entity\QuotaDefinition;
use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PlanQuota> */
final class PlanQuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanQuota::class);
    }

    /** @return PlanQuota[] */
    public function findByPlan(SubscriptionPlan $plan): array
    {
        return $this->findBy(['plan' => $plan]);
    }

    public function findOneByPlanAndQuota(SubscriptionPlan $plan, QuotaDefinition $quota): ?PlanQuota
    {
        return $this->findOneBy(['plan' => $plan, 'quota' => $quota]);
    }

    /** @return array<string, int|null> quota code => limit */
    public function findLimitMapByPlan(SubscriptionPlan $plan): array
    {
        $qb = $this->createQueryBuilder('pq')
            ->select('q.code AS code', 'pq.limitValue AS limitValue')
            ->join('pq.quota', 'q')
            ->andWhere('pq.plan = :plan')
            ->setParameter('plan', $plan);

        $map = [];
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $map[$row['code']] = null === $row['limitValue'] ? null : (int) $row['limitValue'];
        }

        return $map;
    }
}
