<?php

namespace App\State\Permission;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Permission\PermissionInput;
use App\Dto\Permission\PermissionOutput;
use App\Entity\Permission;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PermissionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly PermissionRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?PermissionOutput
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

    private function create(mixed $data): PermissionOutput
    {
        if (!$data instanceof PermissionInput) {
            throw new \InvalidArgumentException('Expected PermissionInput');
        }

        $entity = new Permission(
            code: $data->code,
            name: $data->name,
            module: $data->module,
            description: $data->description,
        );
        $this->em->persist($entity);
        $this->em->flush();

        return (new PermissionProvider($this->repository))->toOutput($entity);
    }

    private function update(string $id, mixed $data): PermissionOutput
    {
        $entity = $this->findEntity($id);

        if (!$data instanceof PermissionInput) {
            throw new \InvalidArgumentException('Expected PermissionInput');
        }

        $entity->setCode($data->code)
            ->setName($data->name)
            ->setModule($data->module)
            ->setDescription($data->description);

        $this->em->flush();

        return (new PermissionProvider($this->repository))->toOutput($entity);
    }

    private function delete(string $id): void
    {
        $entity = $this->findEntity($id);
        $this->em->remove($entity);
        $this->em->flush();
    }

    private function findEntity(string $id): Permission
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Permission not found');
        }

        return $entity;
    }
}
