<?php

namespace App\State\PlatformModule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\PlatformModule\PlatformModuleInput;
use App\Dto\PlatformModule\PlatformModuleOutput;
use App\Entity\PlatformModule;
use App\Entity\SubscriptionPlanModule;
use App\Repository\PlatformModuleRepository;
use App\Repository\SubscriptionPlanModuleRepository;
use App\Service\Module\ModuleCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PlatformModuleProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PlatformModuleRepository $repository,
        private readonly SubscriptionPlanModuleRepository $modules,
        private readonly EntityManagerInterface $em,
        private readonly ModuleCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): PlatformModuleOutput
    {
        if (isset($uriVariables['id'])) {
            return $this->update((string) $uriVariables['id'], $data);
        }

        return $this->create($data);
    }

    private function create(mixed $data): PlatformModuleOutput
    {
        if (!$data instanceof PlatformModuleInput) {
            throw new \InvalidArgumentException('Expected PlatformModuleInput');
        }

        $module = $this->findModule($data->moduleId);

        $entity = new PlatformModule(
            module: $module,
            isEnabled: $data->isEnabled,
            reasonDisabled: $data->reasonDisabled,
        );
        $this->em->persist($entity);
        $this->em->flush();

        $this->cache->deletePlatformModules();

        return $this->toOutput($entity);
    }

    private function update(string $id, mixed $data): PlatformModuleOutput
    {
        $entity = $this->findEntity($id);

        if ($data instanceof PlatformModuleInput) {
            if ($data->moduleId) {
                $module = $this->findModule($data->moduleId);
            }
            $entity->setEnabled($data->isEnabled);
            $entity->setReasonDisabled($data->reasonDisabled);
        } else {
            $entity->setEnabled($data->isEnabled ?? true);
        }

        $this->em->flush();

        $this->cache->deletePlatformModules();

        return $this->toOutput($entity);
    }

    private function findEntity(string $id): PlatformModule
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Platform module not found');
        }

        return $entity;
    }

    private function findModule(string $id): SubscriptionPlanModule
    {
        $entity = $this->modules->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Module not found');
        }

        return $entity;
    }

    private function toOutput(PlatformModule $entity): PlatformModuleOutput
    {
        $output = new PlatformModuleOutput();
        $output->id = (string) $entity->getId();
        $output->moduleId = (string) $entity->getModule()->getId();
        $output->moduleCode = $entity->getModule()->getCode();
        $output->moduleName = $entity->getModule()->getName();
        $output->isEnabled = $entity->isEnabled();
        $output->reasonDisabled = $entity->getReasonDisabled();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
