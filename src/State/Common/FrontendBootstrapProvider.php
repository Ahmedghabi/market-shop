<?php

namespace App\State\Common;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Common\FrontendBootstrapResource;
use App\Enum\BoutiqueStatus;
use App\Repository\BoutiqueRepository;

final class FrontendBootstrapProvider implements ProviderInterface
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly string $projectDir,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): FrontendBootstrapResource
    {
        return new FrontendBootstrapResource(
            boutiques: $this->loadBoutiques(),
            records: [],
            chatMessages: [],
            session: [],
            designTokens: [],
        );
    }

    /** @return array<int, array<string, string>> */
    private function loadBoutiques(): array
    {
        try {
            $active = $this->boutiques->findBy(['status' => BoutiqueStatus::Active], ['createdAt' => 'DESC']);

            if ([] === $active) {
                return [];
            }

            return array_map(function (Boutique\Boutique $boutique): array {
                return [
                    'id' => (string) $boutique->getId(),
                    'name' => $boutique->getName(),
                    'slug' => $boutique->getSlug(),
                    'category' => 'Boutique',
                    'city' => 'En ligne',
                    'image' => '/img/logo.svg',
                    'href' => '/boutiques/'.$boutique->getSlug(),
                ];
            }, $active);
        } catch (\Exception) {
            return [];
        }
    }
}
