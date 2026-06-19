<?php

namespace App\State\SocialProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\SocialProvider\SocialProviderOutput;
use App\Entity\SocialProvider;
use App\Repository\SocialProviderRepository;

final class SocialProviderProvider implements ProviderInterface
{
    public function __construct(
        private readonly SocialProviderRepository $repository,
    ) {
    }

    /** @return array<SocialProviderOutput>|SocialProviderOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SocialProviderOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        return array_map([$this, 'toOutput'], $this->repository->findAll());
    }

    private function toOutput(SocialProvider $entity): SocialProviderOutput
    {
        $output = new SocialProviderOutput();
        $output->id = (string) $entity->getId();
        $output->code = $entity->getCode();
        $output->name = $entity->getName();
        $output->isActive = $entity->isActive();

        return $output;
    }
}
