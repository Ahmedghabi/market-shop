<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Order;
use App\Entity\Shipment;
use App\Enum\ShipmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Shipment> */
final class ShipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shipment::class);
    }

    /** @return list<Shipment> */
    public function findByBoutique(Boutique $boutique, int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.boutique = :boutique')
            ->setParameter('boutique', $boutique)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** @return list<Shipment> */
    public function findAllOrdered(int $limit = 200): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findOneByOrder(Order $order): ?Shipment
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.order = :order')
            ->setParameter('order', $order)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return list<Shipment> */
    public function findNonFinal(int $limit = 200): array
    {
        $finalStatuses = array_map(
            static fn (ShipmentStatus $s) => $s->value,
            array_filter(ShipmentStatus::cases(), static fn (ShipmentStatus $s) => $s->isFinal())
        );

        return $this->createQueryBuilder('s')
            ->andWhere('s.status NOT IN (:final)')
            ->setParameter('final', $finalStatuses)
            ->orderBy('s.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
