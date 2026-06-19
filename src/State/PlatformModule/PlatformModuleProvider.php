<?php

namespace App\State\PlatformModule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\PlatformModule\PlatformModuleOutput;
use App\Entity\PlatformModule;
use App\Repository\PlatformModuleRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlatformModuleOutput> */
final class PlatformModuleProvider implements ProviderInterface
{
    public function __construct(
        private readonly PlatformModuleRepository $repository,
    ) {
    }

    /** @return array<PlatformModuleOutput>|PlatformModuleOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PlatformModuleOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Platform module not found');
            }

            return $this->toOutput($entity);
        }

        $entities = $this->repository->findAll();

        return array_map([$this, 'toOutput'], $entities);
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
