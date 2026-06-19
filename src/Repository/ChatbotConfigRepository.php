<?php

namespace App\Repository;

use App\Entity\Boutique;
use App\Entity\ChatbotConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<ChatbotConfig> */
final class ChatbotConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatbotConfig::class);
    }

    public function findOneByBoutique(Boutique $boutique): ?ChatbotConfig
    {
        return $this->findOneBy(['boutique' => $boutique]);
    }

    public function findEnabledByBoutique(Boutique $boutique): ?ChatbotConfig
    {
        return $this->findOneBy(['boutique' => $boutique, 'isEnabled' => true]);
    }
}
