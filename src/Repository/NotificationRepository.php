<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Notification> */
final class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /** @return list<Notification> */
    public function findForRecipient(?string $recipientIdentifier, bool $isSuperAdmin): array
    {
        $queryBuilder = $this->createQueryBuilder('notification')
            ->orderBy('notification.createdAt', 'DESC');

        if (!$isSuperAdmin) {
            $queryBuilder
                ->andWhere('notification.recipientIdentifier = :recipient')
                ->setParameter('recipient', $recipientIdentifier)
                ->setMaxResults(50);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
