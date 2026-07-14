<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Subscription\SubscriptionSummaryOutput;
use App\Entity\Boutique;
use App\Enum\ExtensionRequestStatus;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\ExtensionRequestRepository;
use App\Repository\QuotaDefinitionRepository;
use App\Security\BoutiqueContext;
use App\Service\Module\ModuleAccessService;
use App\Service\Subscription\SubscriptionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SubscriptionSummaryOutput> */
final class SubscriptionSummaryProvider implements ProviderInterface
{
    public function __construct(
        private readonly BoutiqueContext $context,
        private readonly BoutiqueRepository $boutiques,
        private readonly QuotaDefinitionRepository $quotaDefinitions,
        private readonly BoutiqueExtensionRepository $boutiqueExtensions,
        private readonly ExtensionRequestRepository $extensionRequests,
        private readonly SubscriptionManager $subscriptionManager,
        private readonly ModuleAccessService $moduleAccess,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SubscriptionSummaryOutput
    {
        $boutique = $this->resolveBoutique($context);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        $output = new SubscriptionSummaryOutput();
        $output->boutiqueId = (string) $boutique->getId();
        $output->isActive = $this->subscriptionManager->isSubscriptionActive($boutique);

        $subscription = $boutique->getCurrentSubscription();
        $plan = $this->subscriptionManager->getCurrentPlan($boutique);

        if (null !== $plan) {
            $output->planId = (string) $plan->getId();
            $output->planName = $plan->getName();
            $output->priceTnd = $plan->getPriceTnd();
            $output->currency = $plan->getCurrency();
        }

        if (null !== $subscription) {
            $output->startDate = $subscription->getStartDate()?->format('c');
            $output->endDate = $subscription->getEndDate()?->format('c');
            if (null !== $subscription->getEndDate()) {
                $diff = (new \DateTimeImmutable())->diff($subscription->getEndDate());
                $output->daysRemaining = $subscription->getEndDate() > new \DateTimeImmutable() ? (int) $diff->days : 0;
            }
        }

        $quotas = [];
        foreach ($this->quotaDefinitions->findAllActive() as $quotaDef) {
            $limit = $this->subscriptionManager->getLimit($quotaDef->getCode(), $boutique);
            $usage = $this->subscriptionManager->getUsage($quotaDef->getCode(), $boutique);
            $quotas[] = [
                'code' => $quotaDef->getCode(),
                'name' => $quotaDef->getName(),
                'unit' => $quotaDef->getUnit(),
                'limit' => $limit,
                'usage' => $usage,
                'remaining' => null === $limit ? null : max(0, $limit - $usage),
            ];
        }
        $output->quotas = $quotas;

        $output->accessibleModules = array_map(
            static fn ($m) => $m->getCode(),
            $this->moduleAccess->getAccessibleModules($boutique),
        );

        $output->accessibleThemes = array_map(
            static fn ($theme) => ['id' => (string) $theme->getId(), 'code' => $theme->getCode(), 'name' => $theme->getName()],
            $this->subscriptionManager->getAccessibleThemes($boutique),
        );

        $now = new \DateTimeImmutable();
        $active = [];
        $expiredCount = 0;
        foreach ($this->boutiqueExtensions->findByBoutique($boutique) as $grant) {
            if ($grant->isActive() && !$grant->isExpired($now)) {
                $active[] = [
                    'id' => (string) $grant->getId(),
                    'extensionCode' => $grant->getExtension()->getCode(),
                    'extensionName' => $grant->getExtension()->getName(),
                    'type' => $grant->getExtension()->getType()->value,
                    'activatedAt' => $grant->getActivatedAt()->format('c'),
                    'expiresAt' => $grant->getExpiresAt()?->format('c'),
                ];
            } elseif (!$grant->isActive()) {
                ++$expiredCount;
            }
        }
        $output->activeExtensions = $active;
        $output->expiredExtensionsCount = $expiredCount;

        $pending = [];
        foreach ($this->extensionRequests->findByBoutique($boutique) as $request) {
            if (!\in_array($request->getStatus(), [
                ExtensionRequestStatus::AwaitingPayment,
                ExtensionRequestStatus::Paid,
                ExtensionRequestStatus::AwaitingValidation,
            ], true)) {
                continue;
            }
            $pending[] = [
                'id' => (string) $request->getId(),
                'extensionCode' => $request->getExtension()->getCode(),
                'extensionName' => $request->getExtension()->getName(),
                'status' => $request->getStatus()->value,
                'priceTnd' => $request->getPriceTnd(),
                'requestedAt' => $request->getRequestedAt()->format('c'),
            ];
        }
        $output->pendingRequests = $pending;

        return $output;
    }

    private function resolveBoutique(array $context): ?Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof Request ? $request->attributes->get('_boutique') : null;
        if ($boutique instanceof Boutique) {
            return $boutique;
        }

        $queryBoutiqueId = $request?->query->get('boutiqueId');
        if (\is_string($queryBoutiqueId) && '' !== $queryBoutiqueId && $this->context->isSuperAdmin()) {
            return $this->boutiques->find($queryBoutiqueId);
        }

        $boutiqueId = $this->context->getBoutiqueId();

        return null !== $boutiqueId ? $this->boutiques->find((string) $boutiqueId) : null;
    }
}
