<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyMeOutput;
use App\Repository\CustomerRepository;
use App\Repository\LoyaltyRuleRepository;
use App\Repository\LoyaltyTransactionRepository;
use App\Repository\UserRepository;
use App\Service\Boutique\ShopContext;
use App\Service\Loyalty\LoyaltyEngine;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<LoyaltyMeOutput> */
final readonly class LoyaltyMeProvider implements ProviderInterface
{
    use LoyaltyCustomerResolverTrait;

    public function __construct(
        private ShopContext $shopContext,
        private Security $security,
        private CustomerRepository $customers,
        private UserRepository $users,
        private LoyaltyRuleRepository $rules,
        private LoyaltyTransactionRepository $transactions,
        private LoyaltyEngine $engine,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyMeOutput
    {
        $resolved = $this->resolveCurrentBoutiqueAndCustomer();
        if (null === $resolved) {
            return null;
        }
        [$boutique, $customer] = $resolved;

        $program = $this->engine->getOrCreateProgram($boutique);
        $account = $this->engine->getOrCreateAccount($customer, $boutique);

        $output = new LoyaltyMeOutput();
        $output->programActive = $program->isActive() && $this->engine->isEnabled($boutique);
        $output->boutiqueId = (string) $boutique->getId();
        $output->pointsBalance = $account->getPointsBalance();
        $output->totalEarned = $account->getTotalEarned();
        $output->totalUsed = $account->getTotalUsed();
        $output->pointValueCents = $program->getPointValueCents();
        $output->allowChooseAmount = $program->isAllowChooseAmount();
        $output->allowUseAllPoints = $program->isAllowUseAllPoints();
        $output->allowRewardSelection = $program->isAllowRewardSelection();
        $output->minPointsToRedeem = $program->getMinPointsToRedeem();

        $output->rules = array_map(
            static fn ($rule) => [
                'name' => $rule->getName(),
                'description' => $rule->getDescription(),
                'triggerCode' => $rule->getTriggerCode(),
                'rewardPoints' => $rule->getRewardPoints(),
            ],
            $this->rules->findActiveByProgram($program),
        );

        $output->expiringSoon = array_map(
            static fn ($txn) => [
                'points' => $txn->getRemainingPoints() ?? 0,
                'expiresAt' => $txn->getExpiresAt()?->format('c') ?? '',
            ],
            $this->transactions->findExpiringSoon($account),
        );

        return $output;
    }
}
