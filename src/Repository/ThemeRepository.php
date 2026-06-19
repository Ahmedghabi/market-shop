<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Theme> */
final class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    /** @return Theme[] */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true]);
    }

    public function findDefault(): ?Theme
    {
        return $this->findOneBy(['isDefault' => true]);
    }

    public function findOneByCode(string $code): ?Theme
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function clearDefault(?string $excludeId = null): void
    {
        $qb = $this->createQueryBuilder('t')
            ->update()
            ->set('t.isDefault', ':val')
            ->setParameter('val', false);

        if (null !== $excludeId) {
            $qb->where('t.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        $qb->getQuery()->execute();
    }
}
