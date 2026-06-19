<?php

namespace App\State\Permission;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Permission\PermissionOutput;
use App\Entity\Permission;
use App\Repository\PermissionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PermissionOutput> */
final class PermissionProvider implements ProviderInterface
{
    public function __construct(
        private readonly PermissionRepository $repository,
    ) {
    }

    /** @return array<PermissionOutput>|PermissionOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|PermissionOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Permission not found');
            }

            return $this->toOutput($entity);
        }

        $module = $context['filters']['module'] ?? null;
        if ($module) {
            $entities = $this->repository->findByModule($module);
        } else {
            $entities = $this->repository->findBy([], ['module' => 'ASC', 'code' => 'ASC']);
        }

        return array_map([$this, 'toOutput'], $entities);
    }

    public function toOutput(Permission $entity): PermissionOutput
    {
        $output = new PermissionOutput();
        $output->id = (string) $entity->getId();
        $output->code = $entity->getCode();
        $output->name = $entity->getName();
        $output->module = $entity->getModule();
        $output->description = $entity->getDescription();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
