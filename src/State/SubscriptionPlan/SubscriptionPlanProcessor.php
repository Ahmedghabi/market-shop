<?php

namespace App\State\SubscriptionPlan;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SubscriptionPlan\SubscriptionPlanInput;
use App\Dto\SubscriptionPlan\SubscriptionPlanOutput;
use App\Entity\SubscriptionPlan;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SubscriptionPlanProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SubscriptionPlanRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SubscriptionPlanOutput
    {
        if ($operation instanceof Delete) {
            $entity = $this->findEntity((string) ($uriVariables['id'] ?? ''));
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            return $this->update((string) $uriVariables['id'], $data);
        }

        return $this->create($data);
    }

    private function create(mixed $data): SubscriptionPlanOutput
    {
        if (!$data instanceof SubscriptionPlanInput) {
            throw new \InvalidArgumentException('Expected SubscriptionPlanInput');
        }

        $entity = new SubscriptionPlan(
            name: $data->name,
            description: $data->description,
            durationMonths: $data->durationMonths,
            priceTnd: $data->priceTnd,
            isFree: $data->isFree,
            isVisible: $data->isVisible,
            isActive: $data->isActive,
            modules: $data->modules,
        );
        $this->em->persist($entity);
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function update(string $id, mixed $data): SubscriptionPlanOutput
    {
        $entity = $this->findEntity($id);

        if (!$data instanceof SubscriptionPlanInput) {
            throw new \InvalidArgumentException('Expected SubscriptionPlanInput');
        }

        $entity->setName($data->name);
        $entity->setDescription($data->description);
        $entity->setDurationMonths($data->durationMonths);
        $entity->setPriceTnd($data->priceTnd);
        $entity->setIsFree($data->isFree);
        $entity->setIsVisible($data->isVisible);
        $entity->setIsActive($data->isActive);
        $entity->setModules($data->modules);
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function findEntity(string $id): SubscriptionPlan
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Subscription plan not found');
        }

        return $entity;
    }

    private function toOutput(SubscriptionPlan $entity): SubscriptionPlanOutput
    {
        $output = new SubscriptionPlanOutput();
        $output->id = (string) $entity->getId();
        $output->name = $entity->getName();
        $output->description = $entity->getDescription();
        $output->durationMonths = $entity->getDurationMonths();
        $output->priceTnd = $entity->getPriceTnd();
        $output->isFree = $entity->isFree();
        $output->isVisible = $entity->isVisible();
        $output->isActive = $entity->isActive();
        $output->modules = $entity->getModules();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
