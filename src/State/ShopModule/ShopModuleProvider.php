<?php

namespace App\State\ShopModule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ShopModule\ShopModuleOutput;
use App\Entity\Boutique;
use App\Entity\ShopModule;
use App\Repository\BoutiqueRepository;
use App\Repository\ShopModuleRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ShopModuleOutput> */
final class ShopModuleProvider implements ProviderInterface
{
    public function __construct(
        private readonly ShopModuleRepository $repository,
        private readonly BoutiqueRepository $boutiques,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<ShopModuleOutput>|ShopModuleOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ShopModuleOutput|null
    {
        $boutique = $this->findBoutique((string) ($uriVariables['boutiqueId'] ?? ''));

        if (!$this->context->canAccessBoutique($boutique)) {
            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity || (string) $entity->getBoutique()->getId() !== (string) $boutique->getId()) {
                return null;
            }

            return $this->toOutput($entity);
        }

        $entities = $this->repository->findByBoutique($boutique);

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(ShopModule $entity): ShopModuleOutput
    {
        $output = new ShopModuleOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->moduleId = (string) $entity->getModule()->getId();
        $output->moduleCode = $entity->getModule()->getCode();
        $output->moduleName = $entity->getModule()->getName();
        $output->moduleCategory = $entity->getModule()->getCategory();
        $output->isEnabled = $entity->isEnabled();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }

    private function findBoutique(string $id): Boutique
    {
        $entity = $this->boutiques->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $entity;
    }
}
