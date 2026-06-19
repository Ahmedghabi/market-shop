<?php

namespace App\State\Cms;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Cms\CmsBlockOutput;
use App\Entity\CmsBlock;
use App\Repository\CmsBlockRepository;

/** @implements ProviderInterface<CmsBlockOutput> */
final readonly class CmsBlockProvider implements ProviderInterface
{
    public function __construct(
        private CmsBlockRepository $blocks,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|CmsBlockOutput|null
    {
        unset($context);

        if ($operation instanceof Get) {
            $block = $this->blocks->find((string) ($uriVariables['id'] ?? ''));

            return $block instanceof CmsBlock ? $this->toOutput($block) : null;
        }

        return array_map(
            [$this, 'toOutput'],
            $this->blocks->findAll(),
        );
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
