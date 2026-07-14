<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\ExtensionRequest;
use App\Enum\ExtensionRequestStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ExtensionRequest> */
final class ExtensionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtensionRequest::class);
    }

    /** @return ExtensionRequest[] */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['requestedAt' => 'DESC']);
    }

    /** @return ExtensionRequest[] */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['requestedAt' => 'DESC']);
    }

    /** @return array<string, int> status => count */
    public function countByStatus(): array
    {
        $qb = $this->createQueryBuilder('er')
            ->select('er.status AS status', 'COUNT(er.id) AS cnt')
            ->groupBy('er.status');

        $map = [];
        foreach (ExtensionRequestStatus::cases() as $case) {
            $map[$case->value] = 0;
        }
        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $map[$row['status']] = (int) $row['cnt'];
        }

        return $map;
    }

    /** @return array{code: string, name: string, count: int}[] */
    public function findMostRequestedExtensions(int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('er')
            ->select('e.code AS code', 'e.name AS name', 'COUNT(er.id) AS cnt')
            ->join('er.extension', 'e')
            ->groupBy('e.id, e.code, e.name')
            ->orderBy('cnt', 'DESC')
            ->setMaxResults($limit);

        return array_map(
            static fn (array $row) => ['code' => $row['code'], 'name' => $row['name'], 'count' => (int) $row['cnt']],
            $qb->getQuery()->getScalarResult(),
        );
    }

    public function sumActivatedRevenue(): int
    {
        $qb = $this->createQueryBuilder('er')
            ->select('SUM(er.priceTnd) AS total')
            ->andWhere('er.status = :status')
            ->setParameter('status', ExtensionRequestStatus::Activated);

        $result = $qb->getQuery()->getSingleScalarResult();

        return null === $result ? 0 : (int) $result;
    }
}
