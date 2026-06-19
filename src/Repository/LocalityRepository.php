<?php

namespace App\Repository;

use App\Entity\Governorate;
use App\Entity\Locality;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Locality>
 */
class LocalityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Locality::class);
    }

    /** @return Locality[] */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true], ['name' => 'ASC']);
    }

    /** @return Locality[] */
    public function findByGovernorate(Governorate $governorate): array
    {
        return $this->findBy(['governorate' => $governorate], ['name' => 'ASC']);
    }

    /** @return Locality[] */
    public function searchByName(string $query): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.name LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('l.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Locality> */
    public function findActiveByGovernorate(?Governorate $governorate, ?string $query = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->andWhere('l.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('l.name', 'ASC');

        if ($governorate instanceof Governorate) {
            $qb
                ->andWhere('l.governorate = :governorate')
                ->setParameter('governorate', $governorate);
        }

        $query = trim((string) $query);
        if ('' !== $query) {
            $qb
                ->andWhere('LOWER(l.name) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower($query).'%');
        }

        /** @var list<Locality> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
