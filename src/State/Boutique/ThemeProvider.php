<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Boutique\ThemeOutput;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<ThemeOutput> */
final readonly class ThemeProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ThemeRepository $themes,
    ) {
    }

    /** @return list<ThemeOutput>|ThemeOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ThemeOutput|null
    {
        $boutique = $this->resolveBoutiqueFromRequest($context);

        if ($boutique) {
            return array_map(
                [$this, 'toOutput'],
                $this->themes->findActive(),
            );
        }

        if ($operation instanceof Get) {
            $theme = $this->themes->find((string) ($uriVariables['id'] ?? ''));

            return $theme instanceof Theme ? $this->toOutput($theme) : null;
        }

        return array_map(
            [$this, 'toOutput'],
            $this->themes->findAll(),
        );
    }

    private function toOutput(Theme $theme): ThemeOutput
    {
        $output = new ThemeOutput();
        $output->id = (string) $theme->getId();
        $output->name = $theme->getName();
        $output->code = $theme->getCode();
        $output->previewImage = $theme->getPreviewImage();
        $output->isActive = $theme->isActive();
        $output->isDefault = $theme->isDefault();
        $output->createdAt = $theme->getCreatedAt();
        $output->updatedAt = $theme->getUpdatedAt();

        return $output;
    }
}
