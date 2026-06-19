<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Boutique\MenuInput;
use App\Dto\Boutique\MenuItemInput;
use App\Dto\Boutique\MenuItemOutput;
use App\Dto\Boutique\MenuOutput;
use App\Entity\Menu;
use App\Entity\MenuItem;
use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Repository\MenuRepository;
use App\Repository\MenuItemRepository;
use App\Security\BoutiqueContext;
use App\Service\FrontOfficeCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<MenuOutput|MenuItemOutput|null> */
final readonly class MenuProcessor implements ProcessorInterface
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private MenuRepository $menus,
        private MenuItemRepository $menuItems,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private FrontOfficeCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): MenuOutput|MenuItemOutput|null
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if ($operation instanceof Delete && isset($uriVariables['menuId'])) {
            return $this->deleteMenuItem($uriVariables, $boutique);
        }

        if ($operation instanceof Delete) {
            return $this->deleteMenu($uriVariables, $boutique);
        }

        if (isset($uriVariables['menuId'])) {
            return $this->handleMenuItem($data, $operation, $uriVariables, $boutique);
        }

        return $this->handleMenu($data, $operation, $uriVariables, $boutique);
    }

    private function deleteMenu(array $uriVariables, Boutique $boutique): null
    {
        $menu = $this->menus->find((string) ($uriVariables['id'] ?? ''));
        if ($menu instanceof Menu && (string) $menu->getBoutique()->getId() === (string) $boutique->getId()) {
            $this->em->remove($menu);
            $this->em->flush();
            $this->cache->invalidateMenus((string) $boutique->getId());
        }

        return null;
    }

    private function deleteMenuItem(array $uriVariables, Boutique $boutique): null
    {
        $item = $this->menuItems->find((string) ($uriVariables['id'] ?? ''));
        if ($item instanceof MenuItem) {
            $this->em->remove($item);
            $this->em->flush();
            $this->cache->invalidateMenus((string) $boutique->getId());
        }

        return null;
    }

    private function handleMenu(mixed $data, Operation $operation, array $uriVariables, Boutique $boutique): MenuOutput
    {
        assert($data instanceof MenuInput);

        if ($operation instanceof \ApiPlatform\Metadata\Patch) {
            $menu = $this->menus->find((string) ($uriVariables['id'] ?? ''));
            if (!$menu instanceof Menu || (string) $menu->getBoutique()->getId() !== (string) $boutique->getId()) {
                throw new NotFoundHttpException('Menu not found');
            }
            $menu->setName($data->name);
            $menu->setPosition($data->position);
            $menu->setIsActive($data->isActive);
        } else {
            $menu = new Menu(
                boutique: $boutique,
                name: $data->name,
                position: $data->position,
                isActive: $data->isActive,
            );
            $this->em->persist($menu);
        }

        $this->em->flush();
        $this->cache->invalidateMenus((string) $boutique->getId());

        return $this->toMenuOutput($menu);
    }

    private function handleMenuItem(mixed $data, Operation $operation, array $uriVariables, Boutique $boutique): MenuItemOutput
    {
        assert($data instanceof MenuItemInput);

        $menu = $this->menus->find((string) ($uriVariables['menuId'] ?? ''));
        if (!$menu instanceof Menu || (string) $menu->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Menu not found');
        }

        if ($operation instanceof \ApiPlatform\Metadata\Patch) {
            $item = $this->menuItems->find((string) ($uriVariables['id'] ?? ''));
            if (!$item instanceof MenuItem || (string) $item->getMenu()->getId() !== (string) $menu->getId()) {
                throw new NotFoundHttpException('Menu item not found');
            }
            $item->setTitle($data->title);
            $item->setType($data->type);
            $item->setTarget($data->target);
            $item->setPosition($data->position);
            $item->setIsActive($data->isActive);
            $this->setItemParent($item, $data->parentId);
        } else {
            $item = new MenuItem(
                menu: $menu,
                title: $data->title,
                type: $data->type,
                target: $data->target,
                position: $data->position,
                isActive: $data->isActive,
            );
            $this->em->persist($item);
            $this->setItemParent($item, $data->parentId);
        }

        $this->em->flush();
        $this->cache->invalidateMenus((string) $boutique->getId());

        return $this->toMenuItemOutput($item);
    }

    private function setItemParent(MenuItem $item, ?string $parentId): void
    {
        if (null === $parentId) {
            $item->setParent(null);

            return;
        }
        $parent = $this->menuItems->find($parentId);
        if ($parent instanceof MenuItem) {
            $item->setParent($parent);
        }
    }

    private function toMenuOutput(Menu $menu): MenuOutput
    {
        $output = new MenuOutput();
        $output->id = (string) $menu->getId();
        $output->boutiqueId = (string) $menu->getBoutique()->getId();
        $output->name = $menu->getName();
        $output->position = $menu->getPosition();
        $output->isActive = $menu->isActive();
        $output->createdAt = $menu->getCreatedAt();
        $output->updatedAt = $menu->getUpdatedAt();
        $output->items = array_map(
            fn ($item) => $this->toMenuItemOutput($item),
            $menu->getItems()->toArray(),
        );

        return $output;
    }

    private function toMenuItemOutput(MenuItem $item): MenuItemOutput
    {
        $output = new MenuItemOutput();
        $output->id = (string) $item->getId();
        $output->menuId = (string) $item->getMenu()->getId();
        $output->title = $item->getTitle();
        $output->type = $item->getType();
        $output->target = $item->getTarget();
        $output->parentId = $item->getParent()?->getId()?->toRfc4122();
        $output->position = $item->getPosition();
        $output->isActive = $item->isActive();
        $output->createdAt = $item->getCreatedAt();
        $output->updatedAt = $item->getUpdatedAt();

        return $output;
    }
}
