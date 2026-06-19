<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\NotificationTemplate;
use App\Enum\NotificationChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NotificationTemplate> */
final class NotificationTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationTemplate::class);
    }

    public function findActiveTemplate(?Boutique $boutique, string $eventCode, NotificationChannel $channel): ?NotificationTemplate
    {
        if ($boutique) {
            $template = $this->findOneBy(['boutique' => $boutique, 'eventCode' => $eventCode, 'channel' => $channel, 'isActive' => true]);
            if ($template instanceof NotificationTemplate) {
                return $template;
            }
        }

        return $this->findOneBy(['boutique' => null, 'eventCode' => $eventCode, 'channel' => $channel, 'isActive' => true]);
    }
}
