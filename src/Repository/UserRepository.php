<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<User> */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /** @return array<User> */
    public function findByRole(string $role): array
    {
        $ids = $this->getEntityManager()->getConnection()
            ->executeQuery('SELECT id FROM app_user WHERE jsonb_exists(roles::jsonb, :role)', ['role' => $role])
            ->fetchFirstColumn();

        if ([] === $ids) {
            return [];
        }

        return $this->findBy(['id' => $ids]);
    }
}
