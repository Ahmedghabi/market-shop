<?php

namespace App\State\PlanQuota;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PlanQuota\PlanQuotaOutput;
use App\Entity\PlanQuota;
use App\Repository\PlanQuotaRepository;
use App\Repository\SubscriptionPlanRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlanQuotaOutput> */
final class PlanQuotaProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlanQuotaRepository $repository,
        private readonly SubscriptionPlanRepository $plans,
    ) {
    }

    /** @return array<PlanQuotaOutput>|PlanQuotaOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PlanQuotaOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Plan quota not found');
            }

            return $this->toOutput($entity);
        }

        if (isset($uriVariables['planId'])) {
            $plan = $this->plans->find($uriVariables['planId']);
            if (!$plan) {
                throw new NotFoundHttpException('Plan not found');
            }

            $entities = $this->repository->findByPlan($plan);

            return array_map([$this, 'toOutput'], $entities);
        }

        return [];
    }

    public function toOutput(PlanQuota $entity): PlanQuotaOutput
    {
        $output = new PlanQuotaOutput();
        $output->id = (string) $entity->getId();
        $output->planId = (string) $entity->getPlan()->getId();
        $output->planName = $entity->getPlan()->getName();
        $output->quotaId = (string) $entity->getQuota()->getId();
        $output->quotaCode = $entity->getQuota()->getCode();
        $output->quotaName = $entity->getQuota()->getName();
        $output->limitValue = $entity->getLimitValue();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
