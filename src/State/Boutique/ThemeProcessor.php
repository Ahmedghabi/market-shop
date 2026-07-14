<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Boutique\ThemeInput;
use App\Dto\Boutique\ThemeOutput;
use App\Entity\Theme;
use App\Repository\BoutiqueRepository;
use App\Repository\ThemeRepository;
use App\Service\FrontOfficeCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<ThemeOutput|null> */
final readonly class ThemeProcessor implements ProcessorInterface
{
    public function __construct(
        private ThemeRepository $themes,
        private BoutiqueRepository $boutiques,
        private EntityManagerInterface $em,
        private FrontOfficeCacheService $cache,
        private ThemeProvider $themeProvider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ThemeOutput
    {
        unset($context);

        if ($operation instanceof Delete) {
            $theme = $this->themes->find((string) ($uriVariables['id'] ?? ''));
            if ($theme instanceof Theme) {
                $this->em->remove($theme);
                $this->em->flush();
            }

            return null;
        }

        assert($data instanceof ThemeInput);

        if (empty($data->name) || empty($data->code)) {
            throw new BadRequestHttpException('Name and code are required');
        }

        $existing = $this->themes->findOneByCode($data->code);
        $themeId = $uriVariables['id'] ?? null;

        if ($themeId) {
            $theme = $this->themes->find($themeId);
            if (!$theme instanceof Theme) {
                throw new NotFoundHttpException('Theme not found');
            }
            if ($existing && (string) $existing->getId() !== $themeId) {
                throw new BadRequestHttpException('Code already exists');
            }
            $theme->setName($data->name);
            $theme->setCode($data->code);
            $theme->setPreviewImage($data->previewImage);
            $theme->setIsActive($data->isActive);
        } else {
            if ($existing) {
                throw new BadRequestHttpException('Code already exists');
            }
            $theme = new Theme(
                name: $data->name,
                code: $data->code,
                previewImage: $data->previewImage,
                isActive: $data->isActive,
            );
            $this->em->persist($theme);
        }

        if ($data->isDefault) {
            $this->themes->clearDefault($themeId);
            $theme->setIsDefault(true);
        } else {
            $theme->setIsDefault(false);
        }

        $this->em->flush();

        foreach ($this->boutiques->findAll() as $boutique) {
            $this->cache->invalidateTheme((string) $boutique->getId());
        }

        return $this->themeProvider->provide(
            new \ApiPlatform\Metadata\Get(),
            ['id' => (string) $theme->getId()],
        );
    }
}
