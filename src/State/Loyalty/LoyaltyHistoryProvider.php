<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyTransactionOutput;
use App\Entity\LoyaltyTransaction;
use App\Repository\CustomerRepository;
use App\Repository\LoyaltyTransactionRepository;
use App\Repository\UserRepository;
use App\Service\Boutique\ShopContext;
use App\Service\Loyalty\LoyaltyEngine;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

/** @implements ProviderInterface<LoyaltyTransactionOutput> */
final readonly class LoyaltyHistoryProvider implements ProviderInterface
{
    use LoyaltyCustomerResolverTrait;

    public function __construct(
        private ShopContext $shopContext,
        private Security $security,
        private CustomerRepository $customers,
        private UserRepository $users,
        private LoyaltyTransactionRepository $transactions,
        private LoyaltyEngine $engine,
    ) {
    }

    /** @return list<LoyaltyTransactionOutput> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $resolved = $this->resolveCurrentBoutiqueAndCustomer();
        if (null === $resolved) {
            return [];
        }
        [$boutique, $customer] = $resolved;

        $account = $this->engine->getOrCreateAccount($customer, $boutique);

        $request = $context['request'] ?? null;
        $limit = $request instanceof Request ? max(1, min(200, (int) $request->query->get('itemsPerPage', 50))) : 50;
        $page = $request instanceof Request ? max(1, (int) $request->query->get('page', 1)) : 1;

        $items = $this->transactions->findByCustomerLoyalty($account, $limit, ($page - 1) * $limit);

        return array_map($this->toOutput(...), $items);
    }

    private function toOutput(LoyaltyTransaction $txn): LoyaltyTransactionOutput
    {
        $output = new LoyaltyTransactionOutput();
        $output->id = (string) $txn->getId();
        $output->type = $txn->getType()->value;
        $output->points = $txn->getPoints();
        $output->discountCents = $txn->getDiscountCents();
        $output->orderId = $txn->getOrder()?->getId()->toRfc4122();
        $output->reason = $txn->getReason();
        $output->expiresAt = $txn->getExpiresAt()?->format('c');
        $output->createdAt = $txn->getCreatedAt();

        return $output;
    }
}
