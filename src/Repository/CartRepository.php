<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Cart;
use App\Enum\CartStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Cart> */
final class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findActiveForBoutique(string $id, Boutique $boutique): ?Cart
    {
        return $this->findOneBy([
            'id' => $id,
            'boutique' => $boutique,
            'status' => CartStatus::Active,
        ]);
    }
}
