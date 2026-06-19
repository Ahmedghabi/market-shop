<?php

namespace App\State\Chat;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ChatbotConfig;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ChatbotConfigProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private BoutiqueRepository $boutiqueRepository,
        private BoutiqueContext $boutiqueContext,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? $this->boutiqueContext->getBoutiqueId();

        if (null === $boutiqueId) {
            return null;
        }

        $boutique = $this->boutiqueRepository->find((string) $boutiqueId);
        if (null === $boutique) {
            return null;
        }

        $repo = $this->em->getRepository(ChatbotConfig::class);
        $config = $repo->findOneBy(['boutique' => $boutique]);

        return $config;
    }
}
