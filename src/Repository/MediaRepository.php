<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Media> */
final class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /** @return Media[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['createdAt' => 'DESC']);
    }

    /** @return Media[] */
    public function findByBoutiqueAndType(Boutique $boutique, MediaType $type): array
    {
        return $this->findBy(
            ['boutique' => $boutique, 'type' => $type],
            ['createdAt' => 'DESC'],
        );
    }

    /** @return string[] all unique paths stored in DB */
    public function findAllPaths(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.path')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'path');
    }

    /** @return string[] all unique thumbnail paths stored in DB */
    public function findAllThumbnailPaths(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.thumbnailPath')
            ->where('m.thumbnailPath IS NOT NULL')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'thumbnailPath');
    }
}
