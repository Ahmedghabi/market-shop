<?php

namespace App\State\QuotaDefinition;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\QuotaDefinition\QuotaDefinitionInput;
use App\Dto\QuotaDefinition\QuotaDefinitionOutput;
use App\Entity\QuotaDefinition;
use App\Repository\QuotaDefinitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class QuotaDefinitionProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly QuotaDefinitionRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?QuotaDefinitionOutput
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

    private function create(mixed $data): QuotaDefinitionOutput
    {
        if (!$data instanceof QuotaDefinitionInput) {
            throw new \InvalidArgumentException('Expected QuotaDefinitionInput');
        }

        $entity = new QuotaDefinition(
            code: $data->code,
            name: $data->name,
            description: $data->description,
            unit: $data->unit,
            category: $data->category,
            icon: $data->icon,
            isActive: $data->isActive,
        );
        $this->em->persist($entity);
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function update(string $id, mixed $data): QuotaDefinitionOutput
    {
        $entity = $this->findEntity($id);

        if (!$data instanceof QuotaDefinitionInput) {
            throw new \InvalidArgumentException('Expected QuotaDefinitionInput');
        }

        $entity->setCode($data->code);
        $entity->setName($data->name);
        $entity->setDescription($data->description);
        $entity->setUnit($data->unit);
        $entity->setCategory($data->category);
        $entity->setIcon($data->icon);
        $entity->setIsActive($data->isActive);
        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function findEntity(string $id): QuotaDefinition
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Quota definition not found');
        }

        return $entity;
    }

    private function toOutput(QuotaDefinition $entity): QuotaDefinitionOutput
    {
        $output = new QuotaDefinitionOutput();
        $output->id = (string) $entity->getId();
        $output->code = $entity->getCode();
        $output->name = $entity->getName();
        $output->description = $entity->getDescription();
        $output->unit = $entity->getUnit();
        $output->category = $entity->getCategory();
        $output->icon = $entity->getIcon();
        $output->isActive = $entity->isActive();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
