<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Subscription\PlanChangePreviewOutput;
use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Repository\SubscriptionPlanRepository;
use App\Security\BoutiqueContext;
use App\Service\Subscription\PlanChangePreviewService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<PlanChangePreviewOutput> */
final class PlanChangePreviewProvider implements ProviderInterface
{
    public function __construct(
        private readonly BoutiqueContext $context,
        private readonly BoutiqueRepository $boutiques,
        private readonly SubscriptionPlanRepository $plans,
        private readonly PlanChangePreviewService $previewService,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): PlanChangePreviewOutput
    {
        unset($operation);

        $request = $context['request'] ?? null;
        $planId = $request instanceof Request ? (string) $request->query->get('planId', '') : '';
        if ('' === $planId) {
            throw new BadRequestHttpException('Le paramètre planId est requis.');
        }

        $boutique = $this->resolveBoutique($context);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique introuvable.');
        }

        $plan = $this->plans->find($planId);
        if (null === $plan || !$plan->isActive()) {
            throw new NotFoundHttpException('Plan introuvable ou inactif.');
        }

        return $this->previewService->preview($boutique, $plan);
    }

    private function resolveBoutique(array $context): ?Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof Request ? $request->attributes->get('_boutique') : null;
        if ($boutique instanceof Boutique) {
            return $boutique;
        }

        $boutiqueId = $this->context->getBoutiqueId();

        return null !== $boutiqueId ? $this->boutiques->find((string) $boutiqueId) : null;
    }
}
