<?php

namespace App\State\PlanQuota;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PlanQuota\PlanQuotaInput;
use App\Dto\PlanQuota\PlanQuotaOutput;
use App\Entity\PlanQuota;
use App\Entity\QuotaDefinition;
use App\Entity\SubscriptionPlan;
use App\Repository\PlanQuotaRepository;
use App\Repository\QuotaDefinitionRepository;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PlanQuotaProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PlanQuotaRepository $repository,
        private readonly SubscriptionPlanRepository $plans,
        private readonly QuotaDefinitionRepository $quotas,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?PlanQuotaOutput
    {
        if (isset($uriVariables['id'])) {
            if ('DELETE' === ($context['request']?->getMethod() ?? '')) {
                $this->delete((string) $uriVariables['id']);

                return null;
            }

            return $this->update((string) $uriVariables['id'], $data);
        }

        return $this->create($data);
    }

    private function create(mixed $data): PlanQuotaOutput
    {
        if (!$data instanceof PlanQuotaInput) {
            throw new \InvalidArgumentException('Expected PlanQuotaInput');
        }

        $plan = $this->findPlan($data->planId);
        $quota = $this->findQuota($data->quotaId);

        $existing = $this->repository->findOneByPlanAndQuota($plan, $quota);
        if (null !== $existing) {
            $existing->setLimitValue($data->limitValue);
            $this->em->flush();

            return $this->toProvider()->toOutput($existing);
        }

        $entity = new PlanQuota(
            plan: $plan,
            quota: $quota,
            limitValue: $data->limitValue,
        );
        $this->em->persist($entity);
        $this->em->flush();

        return $this->toProvider()->toOutput($entity);
    }

    private function update(string $id, mixed $data): PlanQuotaOutput
    {
        $entity = $this->findEntity($id);

        if (!$data instanceof PlanQuotaInput) {
            throw new \InvalidArgumentException('Expected PlanQuotaInput');
        }

        $entity->setLimitValue($data->limitValue);
        $this->em->flush();

        return $this->toProvider()->toOutput($entity);
    }

    private function delete(string $id): void
    {
        $entity = $this->findEntity($id);
        $this->em->remove($entity);
        $this->em->flush();
    }

    private function findEntity(string $id): PlanQuota
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Plan quota not found');
        }

        return $entity;
    }

    private function findPlan(string $id): SubscriptionPlan
    {
        $entity = $this->plans->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Plan not found');
        }

        return $entity;
    }

    private function findQuota(string $id): QuotaDefinition
    {
        $entity = $this->quotas->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Quota definition not found');
        }

        return $entity;
    }

    private function toProvider(): PlanQuotaProvider
    {
        return new PlanQuotaProvider($this->repository, $this->plans);
    }
}
