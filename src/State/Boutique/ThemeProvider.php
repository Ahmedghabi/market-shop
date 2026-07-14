<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Boutique\ThemeOutput;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Service\Theme\ThemePresetRegistry;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<ThemeOutput> */
final readonly class ThemeProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ThemeRepository $themes,
        private ThemePresetRegistry $presets,
    ) {
    }

    /** @return list<ThemeOutput>|ThemeOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ThemeOutput|null
    {
        $isAdminOperation = str_starts_with($operation->getUriTemplate() ?? '', '/admin/');
        if ($isAdminOperation) {
            if (isset($uriVariables['id'])) {
                $theme = $this->themes->find((string) $uriVariables['id']);

                return $theme instanceof Theme ? $this->toOutput($theme) : null;
            }

            return array_map([$this, 'toOutput'], $this->themes->findAll());
        }

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
        $preset = $this->presets->get($theme->getCode());

        $output = new ThemeOutput();
        $output->id = (string) $theme->getId();
        $output->name = $theme->getName();
        $output->code = $theme->getCode();
        $output->previewImage = $theme->getPreviewImage();
        $output->isActive = $theme->isActive();
        $output->isDefault = $theme->isDefault();
        $output->description = $preset['description'] ?? null;
        $output->layout = $preset['layout'] ?? null;
        $output->colorPalette = $preset['colorPalette'] ?? [];
        $output->createdAt = $theme->getCreatedAt();
        $output->updatedAt = $theme->getUpdatedAt();

        return $output;
    }
}
