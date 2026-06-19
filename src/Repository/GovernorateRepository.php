<?php

namespace App\Repository;

use App\Entity\Country;
use App\Entity\Governorate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Governorate>
 */
class GovernorateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Governorate::class);
    }

    /** @return Governorate[] */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true], ['name' => 'ASC']);
    }

    public function findOneByCode(string $code): ?Governorate
    {
        /** @var ?Governorate $result */
        $result = $this->findOneBy(['code' => $code]);

        return $result;
    }

    /** @return list<Governorate> */
    public function findActiveByCountry(?Country $country): array
    {
        $qb = $this->createQueryBuilder('g')
            ->andWhere('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.name', 'ASC');

        if ($country instanceof Country) {
            $qb
                ->andWhere('g.country = :country')
                ->setParameter('country', $country);
        }

        /** @var list<Governorate> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
