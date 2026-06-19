<?php

namespace App\Repository;

use App\Entity\Menu;
use App\Entity\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<MenuItem> */
final class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /** @return MenuItem[] */
    public function findByMenu(Menu $menu): array
    {
        return $this->findBy(['menu' => $menu], ['position' => 'ASC']);
    }

    /** @return MenuItem[] */
    public function findRootItemsByMenu(Menu $menu): array
    {
        return $this->findBy(
            ['menu' => $menu, 'parent' => null],
            ['position' => 'ASC'],
        );
    }
}
