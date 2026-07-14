<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyDashboardOutput;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<LoyaltyDashboardOutput> */
final readonly class LoyaltyDashboardProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private LoyaltyEngine $engine,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyDashboardOutput
    {
        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (null === $boutique || !$this->context->canAccessBoutique($boutique)) {
            return null;
        }

        $request = $context['request'] ?? null;
        $from = $request instanceof Request && null !== $request->query->get('from')
            ? new \DateTimeImmutable((string) $request->query->get('from'))
            : null;
        $to = $request instanceof Request && null !== $request->query->get('to')
            ? new \DateTimeImmutable((string) $request->query->get('to'))
            : null;

        $stats = $this->engine->getDashboardStats($boutique, $from, $to);

        $output = new LoyaltyDashboardOutput();
        $output->members = $stats['members'];
        $output->pointsDistributed = $stats['pointsDistributed'];
        $output->pointsUsed = $stats['pointsUsed'];
        $output->pointsExpired = $stats['pointsExpired'];
        $output->rewardsRedeemed = $stats['rewardsRedeemed'];
        $output->programCostCents = $stats['programCostCents'];
        $output->topCustomers = $stats['topCustomers'];

        return $output;
    }
}
