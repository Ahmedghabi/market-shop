<?php

namespace App\State\SubscriptionModule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\SubscriptionModule\SubscriptionModuleOutput;
use App\Entity\SubscriptionModule;
use App\Repository\SubscriptionModuleRepository;
use App\Repository\SubscriptionPlanRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SubscriptionModuleOutput> */
final class SubscriptionModuleProvider implements ProviderInterface
{
    public function __construct(
        private readonly SubscriptionModuleRepository $repository,
        private readonly SubscriptionPlanRepository $plans,
    ) {
    }

    /** @return array<SubscriptionModuleOutput>|SubscriptionModuleOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SubscriptionModuleOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Subscription module not found');
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

    public function toOutput(SubscriptionModule $entity): SubscriptionModuleOutput
    {
        $output = new SubscriptionModuleOutput();
        $output->id = (string) $entity->getId();
        $output->planId = (string) $entity->getPlan()->getId();
        $output->planName = $entity->getPlan()->getName();
        $output->moduleId = (string) $entity->getModule()->getId();
        $output->moduleCode = $entity->getModule()->getCode();
        $output->moduleName = $entity->getModule()->getName();
        $output->isAllowed = $entity->isAllowed();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
