<?php

namespace App\Controller\Rest\Seo;

use App\Repository\BoutiqueRepository;
use App\Repository\CategoryRepository;
use App\Repository\CmsPageRepository;
use App\Repository\ProductRepository;
use App\Service\FrontOfficeCacheService;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class SeoController extends AbstractController
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly CategoryRepository $categories,
        private readonly ProductRepository $products,
        private readonly CmsPageRepository $pages,
        private readonly FrontOfficeCacheService $cache,
        private readonly SeoService $seo,
    ) {
    }

    #[Route('/api/boutiques/{boutiqueId}/seo', name: 'api_boutique_seo_show', methods: ['GET'])]
    public function show(string $boutiqueId): JsonResponse
    {
        $boutique = $this->findBoutique($boutiqueId);

        return $this->json($this->cache->getSeo((string) $boutique->getId()) ?? []);
    }

    #[Route('/api/boutiques/{boutiqueId}/seo/robots.txt', name: 'api_boutique_seo_robots', methods: ['GET'])]
    public function robots(string $boutiqueId): Response
    {
        $boutique = $this->findBoutique($boutiqueId);
        $seo = $this->cache->getSeo((string) $boutique->getId()) ?? $this->seo->buildShopSeo($boutique);

        return new Response((string) ($seo['robots_txt'] ?? $this->seo->defaultRobotsTxt($boutique)), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    #[Route('/api/boutiques/{boutiqueId}/seo/sitemap.xml', name: 'api_boutique_seo_sitemap', methods: ['GET'])]
    public function sitemap(string $boutiqueId): Response
    {
        $boutique = $this->findBoutique($boutiqueId);
        $urls = [[
            'loc' => $this->seo->canonicalUrl($boutique),
            'lastmod' => $boutique->getUpdatedAt()?->format('Y-m-d') ?? $boutique->getCreatedAt()->format('Y-m-d'),
            'priority' => '1.0',
        ]];

        foreach ($this->categories->findSeoIndexedByBoutique($boutique) as $category) {
            $urls[] = [
                'loc' => $this->seo->canonicalUrl($boutique, $category->getSlug()),
                'lastmod' => $category->getUpdatedAt()?->format('Y-m-d') ?? $category->getCreatedAt()->format('Y-m-d'),
                'priority' => '0.8',
            ];
        }

        foreach ($this->products->findSeoIndexedByBoutique($boutique) as $product) {
            $urls[] = [
                'loc' => $this->seo->canonicalUrl($boutique, $product->getSlug()),
                'lastmod' => $product->getUpdatedAt()?->format('Y-m-d') ?? $product->getCreatedAt()->format('Y-m-d'),
                'priority' => '0.9',
            ];
        }

        foreach ($this->pages->findPublishedByBoutique($boutique) as $page) {
            $urls[] = [
                'loc' => $page->getCanonicalUrl() ?: $this->seo->canonicalUrl($boutique, $page->getSlug()),
                'lastmod' => $page->getUpdatedAt()?->format('Y-m-d') ?? $page->getCreatedAt()->format('Y-m-d'),
                'priority' => $page->isHomepage() ? '1.0' : '0.7',
            ];
        }

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.$this->escapeXml($url['loc']).'</loc>';
            $xml[] = '    <lastmod>'.$this->escapeXml($url['lastmod']).'</lastmod>';
            $xml[] = '    <priority>'.$this->escapeXml($url['priority']).'</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return new Response(implode("\n", $xml), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function findBoutique(string $identifier): \App\Entity\Boutique
    {
        $boutique = $this->boutiques->findBySlugOrId($identifier);
        if (null === $boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $boutique;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
