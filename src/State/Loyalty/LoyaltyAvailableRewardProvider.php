<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyAvailableRewardOutput;
use App\Entity\LoyaltyReward;
use App\Enum\LoyaltyCostType;
use App\Repository\CustomerRepository;
use App\Repository\LoyaltyRewardRepository;
use App\Repository\LoyaltyTransactionRepository;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use App\Service\Boutique\ShopContext;
use App\Service\Loyalty\LoyaltyEngine;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<LoyaltyAvailableRewardOutput> */
final readonly class LoyaltyAvailableRewardProvider implements ProviderInterface
{
    use LoyaltyCustomerResolverTrait;

    public function __construct(
        private ShopContext $shopContext,
        private Security $security,
        private CustomerRepository $customers,
        private UserRepository $users,
        private LoyaltyRewardRepository $rewards,
        private LoyaltyTransactionRepository $transactions,
        private OrderRepository $orders,
        private LoyaltyEngine $engine,
    ) {
    }

    /** @return list<LoyaltyAvailableRewardOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $resolved = $this->resolveCurrentBoutiqueAndCustomer();
        if (null === $resolved) {
            return [];
        }
        [$boutique, $customer] = $resolved;

        $program = $this->engine->getOrCreateProgram($boutique);
        if (!$program->isActive() || !$program->isAllowRewardSelection()) {
            return [];
        }

        $account = $this->engine->getOrCreateAccount($customer, $boutique);
        $orderCount = $this->orders->countValidByCustomer($customer);

        return array_map(
            fn (LoyaltyReward $reward) => $this->toOutput($reward, $account, $orderCount),
            $this->rewards->findActiveByProgram($program),
        );
    }

    private function toOutput(LoyaltyReward $reward, \App\Entity\CustomerLoyalty $account, int $orderCount): LoyaltyAvailableRewardOutput
    {
        $output = new LoyaltyAvailableRewardOutput();
        $output->id = (string) $reward->getId();
        $output->name = $reward->getName();
        $output->description = $reward->getDescription();
        $output->typeCode = $reward->getTypeCode();
        $output->costType = $reward->getCostType()->value;
        $output->costValue = $reward->getCostValue();

        [$eligible, $reason] = $this->checkEligibility($reward, $account, $orderCount);
        $output->eligible = $eligible;
        $output->reasonIneligible = $reason;

        return $output;
    }

    /** @return array{0: bool, 1: ?string} */
    private function checkEligibility(LoyaltyReward $reward, \App\Entity\CustomerLoyalty $account, int $orderCount): array
    {
        if (null !== $reward->getMinOrdersRequired() && $orderCount < $reward->getMinOrdersRequired()) {
            return [false, 'Nombre de commandes insuffisant'];
        }

        if (LoyaltyCostType::Points === $reward->getCostType()) {
            if ($account->getPointsBalance() < $reward->getCostValue()) {
                return [false, 'Solde de points insuffisant'];
            }
        } elseif ($orderCount < $reward->getCostValue()) {
            return [false, 'Nombre de commandes insuffisant'];
        }

        if (null !== $reward->getUsageLimit() && $this->transactions->countRewardUsage($reward) >= $reward->getUsageLimit()) {
            return [false, "Cette récompense n'est plus disponible"];
        }

        if (null !== $reward->getUsageLimitPerCustomer() && $this->transactions->countRewardUsageByCustomer($reward, $account) >= $reward->getUsageLimitPerCustomer()) {
            return [false, 'Vous avez atteint la limite d\'utilisation pour cette récompense'];
        }

        return [true, null];
    }
}
