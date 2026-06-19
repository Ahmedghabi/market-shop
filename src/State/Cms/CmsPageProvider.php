<?php

namespace App\State\Cms;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Cms\CmsBlockOutput;
use App\Dto\Cms\CmsPageOutput;
use App\Entity\CmsBlock;
use App\Entity\CmsPage;
use App\Repository\BoutiqueRepository;
use App\Repository\CmsPageRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<CmsPageOutput|CmsBlockOutput> */
final readonly class CmsPageProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private CmsPageRepository $pages,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|CmsPageOutput|CmsBlockOutput|null
    {
        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (!$boutique) {
            if (!$this->context->isSuperAdmin()) {
                return $operation instanceof Get ? null : [];
            }

            if ($operation instanceof Get) {
                $page = $this->pages->find((string) ($uriVariables['id'] ?? ''));

                return $page instanceof CmsPage ? $this->toPageOutput($page) : null;
            }

            return array_map(
                [$this, 'toPageOutput'],
                $this->pages->findBy([], ['sortOrder' => 'ASC', 'createdAt' => 'DESC']),
            );
        }

        if ($operation instanceof Get && isset($uriVariables['blockId'])) {
            return $this->getBlockOutput($boutique, $uriVariables);
        }

        if ($operation instanceof GetCollection && isset($uriVariables['id'])) {
            return $this->getBlockCollectionOutput($boutique, $uriVariables);
        }

        if ($operation instanceof Get) {
            $page = $this->pages->find((string) ($uriVariables['id'] ?? ''));

            return $page instanceof CmsPage && (string) $page->getBoutique()->getId() === (string) $boutique->getId()
                ? $this->toPageOutput($page)
                : null;
        }

        return array_map(
            [$this, 'toPageOutput'],
            $this->pages->findByBoutique($boutique),
        );
    }

    private function getBlockOutput(array $uriVariables): ?CmsBlockOutput
    {
        $page = $this->pages->find((string) ($uriVariables['id'] ?? ''));
        if (!$page) {
            return null;
        }

        $block = $page->getBlocks()->filter(
            fn (CmsBlock $b) => (string) $b->getId() === (string) ($uriVariables['blockId'] ?? ''),
        )->first();

        return $block ? $this->toBlockOutput($block) : null;
    }

    /** @return list<CmsBlockOutput> */
    private function getBlockCollectionOutput(\App\Entity\Boutique $boutique, array $uriVariables): array
    {
        $page = $this->pages->find((string) ($uriVariables['id'] ?? ''));
        if (!$page || (string) $page->getBoutique()->getId() !== (string) $boutique->getId()) {
            return [];
        }

        return array_map(
            [$this, 'toBlockOutput'],
            $page->getBlocks()->toArray(),
        );
    }

    private function toPageOutput(CmsPage $page): CmsPageOutput
    {
        $output = new CmsPageOutput();
        $output->id = (string) $page->getId();
        $output->boutiqueId = (string) $page->getBoutique()->getId();
        $output->title = $page->getTitle();
        $output->slug = $page->getSlug();
        $output->type = $page->getType()->value;
        $output->status = $page->getStatus()->value;
        $output->description = $page->getDescription();
        $output->content = $page->getContent();
        $output->template = $page->getTemplate();
        $output->isHomepage = $page->isHomepage();
        $output->showInHeader = $page->showInHeader();
        $output->showInFooter = $page->showInFooter();
        $output->sortOrder = $page->getSortOrder();
        $output->publishedAt = $page->getPublishedAt();
        $output->metaTitle = $page->getMetaTitle();
        $output->metaDescription = $page->getMetaDescription();
        $output->metaKeywords = $page->getMetaKeywords();
        $output->ogTitle = $page->getOgTitle();
        $output->ogDescription = $page->getOgDescription();
        $output->ogImage = $page->getOgImage();
        $output->canonicalUrl = $page->getCanonicalUrl();
        $output->createdAt = $page->getCreatedAt();
        $output->updatedAt = $page->getUpdatedAt();
        $output->blocks = array_map(
            [$this, 'toBlockOutput'],
            $page->getBlocks()->toArray(),
        );

        return $output;
    }

    private function toBlockOutput(CmsBlock $block): CmsBlockOutput
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
