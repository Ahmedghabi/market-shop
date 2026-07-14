<?php

namespace App\Controller\Rest;

use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductViewDailyRepository;
use App\Security\BoutiqueContext;
use App\Service\Dashboard\DashboardCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ProductViewController
{
    public function __construct(
        private ProductRepository $products,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private EntityManagerInterface $em,
        private ProductViewDailyRepository $dailyViews,
        private DashboardCacheService $dashboardCache,
    ) {
    }

    #[Route('/api/products/{productId}/view', name: 'api_product_view', methods: ['POST'])]
    public function record(string $productId, Request $request): JsonResponse
    {
        $boutique = $request->attributes->get('_boutique');
        if (!$boutique instanceof Boutique) {
            $boutiqueId = $request->query->get('boutiqueId');
            $boutiqueSlug = $request->query->get('boutiqueSlug');
            if (is_string($boutiqueId) && '' !== $boutiqueId && $this->context->isSuperAdmin()) {
                $boutique = $this->boutiques->findBySlugOrId($boutiqueId);
            } elseif (is_string($boutiqueSlug) && '' !== $boutiqueSlug) {
                $boutique = $this->boutiques->findBySlug($boutiqueSlug);
            }
        }

        $product = $this->products->findBySlugOrId($productId, $boutique instanceof Boutique ? $boutique : null);
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $this->em->wrapInTransaction(function () use ($product): void {
            $product->incrementViews();
            $this->dailyViews->incrementForProduct($product, new \DateTimeImmutable('today'));
            $this->em->flush();
        });
        $this->dashboardCache->clearPlatform();
        $this->dashboardCache->clearBoutique((string) $product->getBoutique()->getId());

        return new JsonResponse(['productId' => (string) $product->getId(), 'viewsCount' => $product->getViewsCount()]);
    }
}
