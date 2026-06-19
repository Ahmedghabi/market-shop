<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\Invoice;
use App\Entity\Order;
use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Invoice> */
final class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /** @return list<Invoice> */
    public function findByBoutique(Boutique $boutique): array
    {
        return $this->findBy(['boutique' => $boutique], ['createdAt' => 'DESC']);
    }

    public function findOneByOrder(Order $order): ?Invoice
    {
        return $this->findOneBy(['order' => $order]);
    }

    public function findOneBySubscription(Subscription $subscription): ?Invoice
    {
        return $this->findOneBy(['subscription' => $subscription]);
    }

    public function nextSequence(string $prefix, int $year): int
    {
        $start = sprintf('%s-%d-', $prefix, $year);

        $result = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.invoiceNumber LIKE :pattern')
            ->setParameter('pattern', $start.'%')
            ->getQuery()
            ->getSingleScalarResult();

        return ((int) $result) + 1;
    }
}
