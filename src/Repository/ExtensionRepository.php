<?php

namespace App\Repository;

use App\Entity\Extension;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Extension> */
final class ExtensionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Extension::class);
    }

    public function findOneByCode(string $code): ?Extension
    {
        return $this->findOneBy(['code' => $code]);
    }

    /** @return Extension[] */
    public function findAllActive(): array
    {
        return $this->findBy(['isActive' => true], ['name' => 'ASC']);
    }
}
