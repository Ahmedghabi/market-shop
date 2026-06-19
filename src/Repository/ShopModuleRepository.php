<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\ShopModule;
use App\Entity\SubscriptionPlanModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ShopModule> */
final class ShopModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShopModule::class);
    }

    public function findOneByBoutiqueAndModule(Boutique $boutique, SubscriptionPlanModule $module): ?ShopModule
    {
        return $this->findOneBy(['boutique' => $boutique, 'module' => $module]);
    }

    /** @return ShopModule[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['createdAt' => 'ASC']);
    }

    /** @return ShopModule[] */
    public function findEnabledByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique, 'isEnabled' => true]);
    }
}
