<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Boutique\MenuItemOutput;
use App\Dto\Boutique\MenuOutput;
use App\Entity\Menu;
use App\Repository\BoutiqueRepository;
use App\Repository\MenuRepository;

/** @implements ProviderInterface<MenuOutput|MenuItemOutput> */
final readonly class MenuProvider implements ProviderInterface
{
    public function __construct(
        private MenuRepository $menus,
        private BoutiqueRepository $boutiques,
    ) {
    }

    /** @return list<MenuOutput>|MenuOutput|MenuItemOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|MenuOutput|MenuItemOutput|null
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            return $operation instanceof Get ? null : [];
        }

        if ($operation instanceof Get) {
            $menu = $this->menus->find((string) ($uriVariables['id'] ?? ''));
            if ($menu instanceof Menu && (string) $menu->getBoutique()->getId() === (string) $boutique->getId()) {
                return $this->toMenuOutput($menu);
            }

            return null;
        }

        return array_map(
            fn (Menu $m) => $this->toMenuOutput($m),
            $this->menus->findActiveByBoutique($boutique),
        );
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

    private function toMenuItemOutput(\App\Entity\MenuItem $item): MenuItemOutput
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
