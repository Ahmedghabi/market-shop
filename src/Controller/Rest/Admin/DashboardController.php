<?php

namespace App\Controller\Rest\Admin;

use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Dashboard\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    public function __construct(
        private DashboardService $dashboard,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
    ) {
    }

    #[Route('/api/admin/dashboard/platform', name: 'api_admin_dashboard_platform', methods: ['GET'])]
    public function platform(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return new JsonResponse($this->dashboard->platformOverview());
    }

    #[Route('/api/admin/boutiques/{boutiqueId}/dashboard', name: 'api_admin_dashboard_boutique', methods: ['GET'])]
    public function boutique(string $boutiqueId): JsonResponse
    {
        $boutique = $this->boutiques->findBySlugOrId($boutiqueId);
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        return new JsonResponse($this->dashboard->boutiqueOverview((string) $boutique->getId()));
    }
}
