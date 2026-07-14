<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Subscription\SubscriptionStatsOutput;
use App\Enum\Subscription\SubscriptionRequestStatus as PlanRequestStatus;
use App\Enum\SubscriptionStatus;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\ExtensionRequestRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriptionRequestRepository;

/** @implements ProviderInterface<SubscriptionStatsOutput> */
final class SubscriptionStatsProvider implements ProviderInterface
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions,
        private readonly SubscriptionRequestRepository $subscriptionRequests,
        private readonly ExtensionRequestRepository $extensionRequests,
        private readonly BoutiqueExtensionRepository $boutiqueExtensions,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): SubscriptionStatsOutput
    {
        $output = new SubscriptionStatsOutput();

        $output->activeSubscriptions = $this->subscriptions->countByStatus(SubscriptionStatus::Active);
        $output->expiredSubscriptions = $this->subscriptions->countByStatus(SubscriptionStatus::Expired);
        $output->pendingSubscriptionRequests = $this->subscriptionRequests->count(['status' => PlanRequestStatus::Pending]);
        $output->revenueSubscriptionsTnd = $this->subscriptions->sumActiveRevenue();

        $output->revenueExtensionsTnd = $this->extensionRequests->sumActivatedRevenue();
        $output->extensionRequestsByStatus = $this->extensionRequests->countByStatus();
        $output->mostRequestedExtensions = $this->extensionRequests->findMostRequestedExtensions();

        $output->activeExtensionGrants = $this->boutiqueExtensions->count(['isActive' => true]);
        $output->expiredExtensionGrants = $this->boutiqueExtensions->count(['isActive' => false]);

        return $output;
    }
}
