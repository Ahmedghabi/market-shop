<?php

namespace App\State\ShopModule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\ShopModule\ShopModuleResource;
use App\Dto\ShopModule\ShopModuleOutput;
use App\Entity\Boutique;
use App\Entity\ShopModule;
use App\Entity\SubscriptionPlanModule;
use App\Repository\BoutiqueRepository;
use App\Repository\ShopModuleRepository;
use App\Repository\SubscriptionPlanModuleRepository;
use App\Security\BoutiqueContext;
use App\Service\Module\ModuleCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class ShopModuleProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ShopModuleRepository $repository,
        private readonly SubscriptionPlanModuleRepository $modules,
        private readonly BoutiqueRepository $boutiques,
        private readonly EntityManagerInterface $em,
        private readonly BoutiqueContext $context,
        private readonly ModuleCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ShopModuleOutput
    {
        $boutique = $this->findBoutique((string) ($uriVariables['boutiqueId'] ?? ''));

        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if (isset($uriVariables['id'])) {
            return $this->toggle((string) $uriVariables['id'], $boutique, $data);
        }

        return $this->create($boutique, $data);
    }

    private function create(Boutique $boutique, mixed $data): ShopModuleOutput
    {
        if (!$data instanceof ShopModuleResource) {
            throw new \InvalidArgumentException('Expected ShopModuleResource');
        }

        $module = $this->findModule($data->moduleId);

        $entity = new ShopModule(
            boutique: $boutique,
            module: $module,
            isEnabled: $data->isEnabled,
        );
        $this->em->persist($entity);
        $this->em->flush();

        $this->cache->deleteShopModules((string) $boutique->getId());

        return $this->toOutput($entity);
    }

    private function toggle(string $id, Boutique $boutique, mixed $data): ShopModuleOutput
    {
        $entity = $this->findEntity($id, $boutique);

        if ($data instanceof ShopModuleResource) {
            $entity->setEnabled($data->isEnabled);
        }

        $this->em->flush();

        $this->cache->deleteShopModules((string) $boutique->getId());

        return $this->toOutput($entity);
    }

    private function findEntity(string $id, Boutique $boutique): ShopModule
    {
        $entity = $this->repository->find($id);
        if (!$entity || (string) $entity->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Shop module not found');
        }

        return $entity;
    }

    private function findBoutique(string $id): Boutique
    {
        $entity = $this->boutiques->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
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
}
