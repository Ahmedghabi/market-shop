<?php

namespace App\Service\Cms;

use App\Repository\CmsPageRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class CmsCacheService
{
    private const int TTL = 21600; // 6h
    private const string PREFIX = 'shop:';

    public function __construct(
        private CacheInterface $cache,
        private CmsPageRepository $pages,
    ) {
    }

    /** @return array<int, array{id: string, title: string, slug: string, type: string, status: string}> */
    public function getPagesList(string $boutiqueId): array
    {
        $key = self::PREFIX."{$boutiqueId}:cms_pages";

        return $this->cache->get($key, function (ItemInterface $item) use ($boutiqueId): array {
            $item->expiresAfter(self::TTL);

            $boutique = $this->pages->findOneBy(['id' => $boutiqueId]);
            if (!$boutique) {
                return [];
            }

            $pages = $this->pages->findByBoutique($boutique);

            return array_map(fn ($p) => [
                'id' => (string) $p->getId(),
                'title' => $p->getTitle(),
                'slug' => $p->getSlug(),
                'type' => $p->getType()->value,
                'status' => $p->getStatus()->value,
            ], $pages);
        });
    }

    /** @return array<string, mixed>|null */
    public function getPage(string $boutiqueId, string $slug): ?array
    {
        $key = self::PREFIX."{$boutiqueId}:cms:{$slug}";

        return $this->cache->get($key, function (ItemInterface $item) use ($boutiqueId, $slug): ?array {
            $boutique = $this->pages->findOneBy(['id' => $boutiqueId]);
            if (!$boutique) {
                return null;
            }

            $page = $this->pages->findOneByBoutiqueAndSlug($boutique, $slug);
            if (!$page) {
                return null;
            }

            $item->expiresAfter(self::TTL);

            return [
                'id' => (string) $page->getId(),
                'title' => $page->getTitle(),
                'slug' => $page->getSlug(),
                'type' => $page->getType()->value,
                'status' => $page->getStatus()->value,
                'description' => $page->getDescription(),
                'content' => $page->getContent(),
                'template' => $page->getTemplate(),
                'isHomepage' => $page->isHomepage(),
                'showInHeader' => $page->showInHeader(),
                'showInFooter' => $page->showInFooter(),
                'metaTitle' => $page->getMetaTitle(),
                'metaDescription' => $page->getMetaDescription(),
                'metaKeywords' => $page->getMetaKeywords(),
                'ogTitle' => $page->getOgTitle(),
                'ogDescription' => $page->getOgDescription(),
                'ogImage' => $page->getOgImage(),
                'canonicalUrl' => $page->getCanonicalUrl(),
                'publishedAt' => $page->getPublishedAt()?->format('c'),
                'blocks' => array_map(fn ($b) => [
                    'id' => (string) $b->getId(),
                    'type' => $b->getType()->value,
                    'title' => $b->getTitle(),
                    'content' => $b->getContent(),
                    'settings' => $b->getSettings(),
                    'sortOrder' => $b->getSortOrder(),
                    'isActive' => $b->isActive(),
                ], $page->getBlocks()->toArray()),
            ];
        });
    }

    public function invalidate(string $boutiqueId): void
    {
        $this->cache->delete(self::PREFIX."{$boutiqueId}:cms_pages");
    }

    public function invalidatePage(string $boutiqueId, string $pageId): void
    {
        $page = $this->pages->find($pageId);
        if ($page) {
            $this->cache->delete(self::PREFIX."{$boutiqueId}:cms:{$page->getSlug()}");
        }
        $this->invalidate($boutiqueId);
    }
}
