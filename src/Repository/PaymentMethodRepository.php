<?php

namespace App\Repository;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<PaymentMethod> */
final class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    /** @return list<PaymentMethod> */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true], ['name' => 'ASC']);
    }

    /** @return list<PaymentMethod> */
    public function findActiveVisible(): array
    {
        return $this->findBy(['isActive' => true, 'isVisible' => true], ['name' => 'ASC']);
    }

    public function findOneByCode(string $code): ?PaymentMethod
    {
        return $this->findOneBy(['code' => $code]);
    }
}
