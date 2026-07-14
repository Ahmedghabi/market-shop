<?php

namespace App\State\QuotaDefinition;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\QuotaDefinition\QuotaDefinitionOutput;
use App\Entity\QuotaDefinition;
use App\Repository\QuotaDefinitionRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<QuotaDefinitionOutput> */
final class QuotaDefinitionProvider implements ProviderInterface
{
    public function __construct(
        private readonly QuotaDefinitionRepository $repository,
    ) {
    }

    /** @return array<QuotaDefinitionOutput>|QuotaDefinitionOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|QuotaDefinitionOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Quota definition not found');
            }

            return $this->toOutput($entity);
        }

        $entities = $this->repository->findBy([], ['category' => 'ASC', 'name' => 'ASC']);

        return array_map([$this, 'toOutput'], $entities);
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
