<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Menu> */
final class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /** @return Menu[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['position' => 'ASC', 'createdAt' => 'ASC']);
    }

    /** @return Menu[] */
    public function findActiveByBoutique(Boutique $boutique): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'isActive' => true],
            ['position' => 'ASC', 'createdAt' => 'ASC'],
        );
    }
}
