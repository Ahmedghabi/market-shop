<?php

namespace App\State\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\UserShop\UserShopOutput;
use App\Entity\UserShop;
use App\Repository\UserShopRepository;
use App\Security\BoutiqueContext;

final class UserShopProvider implements ProviderInterface
{
    public function __construct(
        private readonly UserShopRepository $repository,
        private readonly BoutiqueContext $boutiqueContext,
    ) {
    }

    /** @return array<UserShopOutput>|UserShopOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|UserShopOutput|null
    {
        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        $boutiqueIds = $this->boutiqueContext->getBoutiqueIds();

        $entities = [];
        foreach ($boutiqueIds as $boutiqueId) {
            $entities = array_merge($entities, $this->repository->findByBoutique((string) $boutiqueId));
        }

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(UserShop $entity): UserShopOutput
    {
        $output = new UserShopOutput();
        $output->id = (string) $entity->getId();
        $output->userId = (string) $entity->getUser()->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->role = $entity->getRole();
        $output->status = $entity->getStatus()->value;
        $output->createdAt = $entity->getCreatedAt();

        return $output;
    }
}
