<?php

namespace App\State\Cms;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cms\CmsBlockInput;
use App\Dto\Cms\CmsBlockOutput;
use App\Entity\CmsBlock;
use App\Enum\CmsBlockType;
use App\Repository\BoutiqueRepository;
use App\Repository\CmsBlockRepository;
use App\Repository\CmsPageRepository;
use App\Security\BoutiqueContext;
use App\Service\Cms\CmsCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<CmsBlockOutput|null> */
final readonly class CmsBlockProcessor implements ProcessorInterface
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private CmsPageRepository $pages,
        private CmsBlockRepository $blocks,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private CmsCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?CmsBlockOutput
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $page = $this->pages->find((string) ($uriVariables['pageId'] ?? ''));
        if (!$page instanceof \App\Entity\CmsPage || (string) $page->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Page not found');
        }

        if ($operation instanceof Delete) {
            $block = $this->blocks->find((string) ($uriVariables['id'] ?? ''));
            if ($block instanceof CmsBlock) {
                $this->em->remove($block);
                $this->em->flush();
                $this->cache->invalidate((string) $boutique->getId());
            }

            return null;
        }

        assert($data instanceof CmsBlockInput);

        $block = new CmsBlock(
            page: $page,
            type: CmsBlockType::tryFrom($data->type ?? 'TEXT') ?? CmsBlockType::Text,
            title: $data->title,
            content: $data->content,
            settings: $data->settings,
            sortOrder: $data->sortOrder,
            isActive: $data->isActive,
        );

        $this->em->persist($block);
        $this->em->flush();
        $this->cache->invalidate((string) $boutique->getId());
        $this->cache->invalidatePage((string) $boutique->getId(), (string) $page->getId());

        return $this->toOutput($block);
    }

    private function toOutput(CmsBlock $block): CmsBlockOutput
    {
        $output = new CmsBlockOutput();
        $output->id = (string) $block->getId();
        $output->pageId = (string) $block->getPage()->getId();
        $output->type = $block->getType()->value;
        $output->title = $block->getTitle();
        $output->content = $block->getContent();
        $output->settings = $block->getSettings();
        $output->sortOrder = $block->getSortOrder();
        $output->isActive = $block->isActive();
        $output->createdAt = $block->getCreatedAt();
        $output->updatedAt = $block->getUpdatedAt();

        return $output;
    }
}
