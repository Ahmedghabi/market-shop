<?php

namespace App\Repository;

use App\Entity\Boutique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/** @extends ServiceEntityRepository<Boutique> */
final class BoutiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boutique::class);
    }

    public function findBySlug(string $slug): ?Boutique
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findBySlugOrId(string $identifier): ?Boutique
    {
        if (Uuid::isValid($identifier)) {
            return $this->find($identifier);
        }

        return $this->findBySlug($identifier);
    }

    /**
     * @param list<Uuid> $ids
     *
     * @return list<Boutique>
     */
    public function findVisibleTo(array $ids, bool $isSuperAdmin): array
    {
        if ($isSuperAdmin) {
            return $this->findBy([], ['createdAt' => 'DESC']);
        }

        if ([] === $ids) {
            return [];
        }

        return $this->createQueryBuilder('boutique')
            ->andWhere('boutique.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('boutique.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Boutique> */
    public function findPublishedForPublic(): array
    {
        return $this->createQueryBuilder('boutique')
            ->innerJoin('boutique.subscriptions', 'subscription')
            ->andWhere('boutique.status = :status')
            ->andWhere('subscription.status = :subStatus')
            ->setParameter('status', \App\Enum\BoutiqueStatus::Active)
            ->setParameter('subStatus', \App\Enum\SubscriptionStatus::Active)
            ->orderBy('boutique.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Boutique> */
    public function findPendingValidation(): array
    {
        return $this->findBy(['status' => \App\Enum\BoutiqueStatus::Pending], ['createdAt' => 'DESC']);
    }
}
