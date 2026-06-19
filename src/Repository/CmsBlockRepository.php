<?php

namespace App\Repository;

use App\Entity\CmsBlock;
use App\Entity\CmsPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<CmsBlock> */
final class CmsBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmsBlock::class);
    }

    /** @return CmsBlock[] */
    public function findByPage(CmsPage $page): array
    {
        return $this->findBy(['page' => $page], ['sortOrder' => 'ASC']);
    }

    /** @return CmsBlock[] */
    public function findActiveByPage(CmsPage $page): array
    {
        return $this->findBy(
            ['page' => $page, 'isActive' => true],
            ['sortOrder' => 'ASC'],
        );
    }
}
