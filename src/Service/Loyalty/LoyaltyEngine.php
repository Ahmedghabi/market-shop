<?php

namespace App\Service\Loyalty;

use App\Entity\Boutique;
use App\Entity\Customer;
use App\Entity\CustomerLoyalty;
use App\Entity\LoyaltyProgram;
use App\Entity\LoyaltyReward;
use App\Entity\LoyaltyRule;
use App\Entity\LoyaltyTransaction;
use App\Entity\Order;
use App\Enum\LoyaltyCostType;
use App\Enum\LoyaltyTransactionType;
use App\Repository\CustomerLoyaltyRepository;
use App\Repository\LoyaltyProgramRepository;
use App\Repository\LoyaltyRewardRepository;
use App\Repository\LoyaltyRuleRepository;
use App\Repository\LoyaltyTransactionRepository;
use App\Repository\OrderRepository;
use App\Service\CustomerNotification\CustomerNotificationService;
use App\Service\Loyalty\Dto\LoyaltyCalculationResult;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRedemptionRequest;
use App\Service\Loyalty\Reward\LoyaltyRewardApplierRegistry;
use App\Service\Loyalty\Trigger\LoyaltyTriggerRegistry;
use App\Service\Module\ModuleAccessService;
use App\Service\Webhook\WebhookService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The ONLY service allowed to compute loyalty points, discounts and balances.
 * Every other module (Cart, Order, Refund, CMS, ...) must go through here.
 */
final readonly class LoyaltyEngine
{
    public function __construct(
        private LoyaltyProgramRepository $programs,
        private LoyaltyRuleRepository $rules,
        private LoyaltyRewardRepository $rewards,
        private CustomerLoyaltyRepository $customerLoyalties,
        private LoyaltyTransactionRepository $transactions,
        private OrderRepository $orders,
        private LoyaltyTriggerRegistry $triggerRegistry,
        private LoyaltyRewardApplierRegistry $rewardApplierRegistry,
        private ModuleAccessService $moduleAccess,
        private WebhookService $webhookService,
        private CustomerNotificationService $notifications,
        private EntityManagerInterface $em,
    ) {
    }

    public function isEnabled(Boutique $boutique): bool
    {
        return $this->moduleAccess->isModuleEnabled('loyalty', $boutique);
    }

    public function getOrCreateProgram(Boutique $boutique): LoyaltyProgram
    {
        $program = $this->programs->findOneByBoutique($boutique);
        if (null !== $program) {
            return $program;
        }

        $program = new LoyaltyProgram($boutique);
        $this->em->persist($program);
        $this->em->flush();

        return $program;
    }

    public function getOrCreateAccount(Customer $customer, Boutique $boutique): CustomerLoyalty
    {
        $account = $this->customerLoyalties->findOneForCustomer($customer, $boutique);
        if (null !== $account) {
            return $account;
        }

        $account = new CustomerLoyalty($customer, $boutique);
        $this->em->persist($account);

        return $account;
    }

    /**
     * Evaluates all active earning rules for a paid order and credits points.
     * Guest checkouts (no Customer) never participate. Idempotent per order.
     */
    public function earnForOrder(Order $order): void
    {
        $boutique = $order->getBoutique();
        $customer = $order->getCustomer();

        // Guest checkouts (no linked Hanooti account) never participate in the loyalty program.
        if (null === $customer || null === $customer->getUser() || !$this->isEnabled($boutique)) {
            return;
        }

        $program = $this->programs->findOneByBoutique($boutique);
        if (null === $program || !$program->isActive()) {
            return;
        }

        if ([] !== $this->transactions->findEarnBatchesForOrder($order)) {
            return; // already processed — idempotent on retry/duplicate dispatch
        }

        $account = $this->getOrCreateAccount($customer, $boutique);
        $context = $this->buildContext($boutique, $customer, $account, $order);

        $activeRules = array_values(array_filter(
            $this->rules->findActiveByProgram($program),
            static fn (LoyaltyRule $r) => $r->isCurrentlyActive($context->now),
        ));
        $normalRules = array_values(array_filter($activeRules, static fn (LoyaltyRule $r) => !$r->isMultiplier()));
        $multiplierRules = array_values(array_filter($activeRules, static fn (LoyaltyRule $r) => $r->isMultiplier()));

        $basePoints = 0;
        $firedRules = [];
        foreach ($normalRules as $rule) {
            $evaluator = $this->triggerRegistry->find($rule->getTriggerCode());
            if (null === $evaluator) {
                continue;
            }

            $points = $evaluator->evaluate($rule, $context);
            if ($points <= 0) {
                continue;
            }

            $basePoints += $points;
            $firedRules[] = $rule;

            if (!$rule->isCumulative()) {
                break; // exclusive rule: stop evaluating lower-priority rules for this event
            }
        }

        if ($basePoints <= 0 || [] === $firedRules) {
            return;
        }

        $multiplier = $this->resolveMultiplier($multiplierRules, $firedRules, $context->now);
        $finalPoints = (int) round($basePoints * $multiplier);
        if ($finalPoints <= 0) {
            return;
        }

        $primaryRule = $firedRules[0];
        $transaction = new LoyaltyTransaction(
            customerLoyalty: $account,
            boutique: $boutique,
            type: LoyaltyTransactionType::Earn,
            points: $finalPoints,
            remainingPoints: $finalPoints,
            order: $order,
            rule: $primaryRule,
            reason: sprintf('Points gagnés pour la commande #%s', (string) $order->getId()),
            expiresAt: $this->computeExpiry($program, $context->now),
        );
        $account->addPoints($finalPoints);
        $this->em->persist($transaction);
        $this->em->flush();

        $this->unlockRewardIfAny($primaryRule, $account);

        $this->webhookService->dispatchEvent('loyalty.points_earned', [
            'boutiqueId' => (string) $boutique->getId(),
            'customerId' => (string) $customer->getId(),
            'orderId' => (string) $order->getId(),
            'points' => $finalPoints,
            'balance' => $account->getPointsBalance(),
        ], (string) $boutique->getId());
    }

    /**
     * Dry-run: validates and computes the effect of a redemption request
     * without persisting anything. Used both by the checkout flow (before
     * order creation) and the "/loyalty/quote" endpoint.
     */
    public function calculateRedemption(
        Boutique $boutique,
        Customer $customer,
        LoyaltyRedemptionRequest $request,
        int $subtotalCents,
        int $alreadyAppliedDiscountsCents = 0,
    ): LoyaltyCalculationResult {
        if ($request->isEmpty()) {
            return LoyaltyCalculationResult::failure('Aucune demande de fidélité.');
        }

        if (null === $customer->getUser()) {
            return LoyaltyCalculationResult::failure('Un compte Hanooti est requis pour utiliser vos points.');
        }

        if (!$this->isEnabled($boutique)) {
            return LoyaltyCalculationResult::failure('Le programme de fidélité est désactivé pour cette boutique.');
        }

        $program = $this->programs->findOneByBoutique($boutique);
        if (null === $program || !$program->isActive()) {
            return LoyaltyCalculationResult::failure('Aucun programme de fidélité actif.');
        }

        $account = $this->customerLoyalties->findOneForCustomer($customer, $boutique);
        if (null === $account || $account->getPointsBalance() <= 0) {
            return LoyaltyCalculationResult::failure('Aucun point de fidélité disponible.');
        }

        if ($subtotalCents < $program->getMinOrderAmountCentsToRedeem()) {
            return LoyaltyCalculationResult::failure('Montant de commande insuffisant pour utiliser vos points.');
        }

        if ($program->getMinOrdersCountToRedeem() > 0 && $this->orders->countValidByCustomer($customer) < $program->getMinOrdersCountToRedeem()) {
            return LoyaltyCalculationResult::failure('Nombre de commandes insuffisant pour utiliser vos points.');
        }

        $context = $this->buildContext($boutique, $customer, $account, null, $subtotalCents, $alreadyAppliedDiscountsCents);

        if (null !== $request->rewardId) {
            return $this->calculateRewardRedemption($program, $account, $request->rewardId, $context);
        }

        return $this->calculateGenericPointsRedemption($program, $account, $request, $context);
    }

    /**
     * Persists the Redeem ledger entry once the order has actually been
     * created — kept separate from calculateRedemption() so nothing is
     * deducted if order creation fails.
     */
    public function confirmRedemption(Order $order, Customer $customer, LoyaltyCalculationResult $result): void
    {
        if (!$result->success || $result->pointsUsed <= 0) {
            return;
        }

        $account = $this->customerLoyalties->findOneForCustomer($customer, $order->getBoutique());
        if (null === $account) {
            return;
        }

        $reward = null !== $result->rewardId ? $this->rewards->find($result->rewardId) : null;

        $transaction = new LoyaltyTransaction(
            customerLoyalty: $account,
            boutique: $order->getBoutique(),
            type: LoyaltyTransactionType::Redeem,
            points: -$result->pointsUsed,
            discountCents: $result->discountCents,
            order: $order,
            reward: $reward instanceof LoyaltyReward ? $reward : null,
            reason: sprintf('Points utilisés pour la commande #%s', (string) $order->getId()),
        );

        $account->usePoints($result->pointsUsed);
        $this->consumeFifo($account, $result->pointsUsed);
        $this->em->persist($transaction);
        $this->em->flush();

        $this->webhookService->dispatchEvent('loyalty.points_redeemed', [
            'boutiqueId' => (string) $order->getBoutique()->getId(),
            'customerId' => (string) $customer->getId(),
            'orderId' => (string) $order->getId(),
            'points' => $result->pointsUsed,
            'discountCents' => $result->discountCents,
        ], (string) $order->getBoutique()->getId());
    }

    /**
     * Applies the configured cancellation policy on order cancellation
     * ($refundRatio = 1.0) or partial refund (ratio = refunded/total).
     */
    public function reverseForOrder(Order $order, float $refundRatio = 1.0): void
    {
        $boutique = $order->getBoutique();
        $customer = $order->getCustomer();
        $program = $this->programs->findOneByBoutique($boutique);

        if (null === $customer || null === $program) {
            return;
        }

        $account = $this->customerLoyalties->findOneForCustomer($customer, $boutique);
        if (null === $account) {
            return;
        }

        $ratio = max(0.0, min(1.0, $refundRatio));
        $changed = false;

        if ($program->isReturnUsedPointsOnCancel()) {
            foreach ($this->transactions->findRedeemBatchesForOrder($order) as $redeemTxn) {
                $pointsToReturn = (int) round(abs($redeemTxn->getPoints()) * $ratio);
                if ($pointsToReturn <= 0) {
                    continue;
                }

                $account->restorePoints($pointsToReturn);
                $this->em->persist(new LoyaltyTransaction(
                    customerLoyalty: $account,
                    boutique: $boutique,
                    type: LoyaltyTransactionType::Cancellation,
                    points: $pointsToReturn,
                    order: $order,
                    reason: 'Restitution des points utilisés (annulation/remboursement)',
                ));
                $changed = true;
            }
        }

        if ($program->isRevokeEarnedPointsOnCancel()) {
            foreach ($this->transactions->findEarnBatchesForOrder($order) as $earnTxn) {
                $available = $earnTxn->getRemainingPoints() ?? 0;
                $pointsToRevoke = min($available, (int) round($earnTxn->getPoints() * $ratio));
                if ($pointsToRevoke <= 0) {
                    continue;
                }

                $earnTxn->consumePoints($pointsToRevoke);
                $account->revokePoints($pointsToRevoke);
                $this->em->persist(new LoyaltyTransaction(
                    customerLoyalty: $account,
                    boutique: $boutique,
                    type: LoyaltyTransactionType::Cancellation,
                    points: -$pointsToRevoke,
                    order: $order,
                    reason: 'Annulation des points gagnés (annulation/remboursement)',
                ));
                $changed = true;
            }
        }

        if ($changed) {
            $this->em->flush();
        }
    }

    /**
     * Cron entry point (ExpireLoyaltyPointsCommand): expires Earn batches
     * past their validity date and writes the corresponding ledger entries.
     */
    public function expirePoints(): int
    {
        $now = new \DateTimeImmutable();
        $expiredBatches = 0;

        foreach ($this->transactions->findExpiredUnprocessed($now) as $earnTxn) {
            $remaining = $earnTxn->getRemainingPoints() ?? 0;
            if ($remaining <= 0) {
                continue;
            }

            $earnTxn->consumePoints($remaining);
            $account = $earnTxn->getCustomerLoyalty();
            $account->revokePoints($remaining);

            $this->em->persist(new LoyaltyTransaction(
                customerLoyalty: $account,
                boutique: $earnTxn->getBoutique(),
                type: LoyaltyTransactionType::Expiration,
                points: -$remaining,
                reason: 'Expiration des points',
            ));
            ++$expiredBatches;
        }

        if ($expiredBatches > 0) {
            $this->em->flush();
        }

        return $expiredBatches;
    }

    /**
     * Generic entry point for other modules (CMS forms, referral, newsletter)
     * to grant/deduct points via a configured manual/custom_event rule
     * without any coupling to LoyaltyEngine internals.
     */
    public function manualAdjustment(Customer $customer, Boutique $boutique, int $points, string $reason): CustomerLoyalty
    {
        $account = $this->getOrCreateAccount($customer, $boutique);
        $program = $this->getOrCreateProgram($boutique);

        $account->correct($points);
        $this->em->persist(new LoyaltyTransaction(
            customerLoyalty: $account,
            boutique: $boutique,
            type: LoyaltyTransactionType::Correction,
            points: $points,
            remainingPoints: $points > 0 ? $points : null,
            reason: $reason,
            expiresAt: $points > 0 ? $this->computeExpiry($program, new \DateTimeImmutable()) : null,
        ));
        $this->em->flush();

        return $account;
    }

    public function dispatchCustomEvent(string $eventCode, Customer $customer, Boutique $boutique, array $payload = []): int
    {
        if (!$this->isEnabled($boutique)) {
            return 0;
        }

        $program = $this->programs->findOneByBoutique($boutique);
        if (null === $program || !$program->isActive()) {
            return 0;
        }

        $account = $this->getOrCreateAccount($customer, $boutique);
        $context = $this->buildContext($boutique, $customer, $account, null, extra: array_merge($payload, ['eventCode' => $eventCode]));

        $totalPoints = 0;
        foreach ($this->rules->findActiveByProgram($program) as $rule) {
            if ($rule->isMultiplier() || !$rule->isCurrentlyActive($context->now)) {
                continue;
            }

            $evaluator = $this->triggerRegistry->find($rule->getTriggerCode());
            if (null === $evaluator) {
                continue;
            }

            $points = $evaluator->evaluate($rule, $context);
            if ($points > 0) {
                $totalPoints += $points;
                $this->em->persist(new LoyaltyTransaction(
                    customerLoyalty: $account,
                    boutique: $boutique,
                    type: LoyaltyTransactionType::Earn,
                    points: $points,
                    remainingPoints: $points,
                    rule: $rule,
                    reason: sprintf('Événement personnalisé: %s', $eventCode),
                    expiresAt: $this->computeExpiry($program, $context->now),
                ));
                $account->addPoints($points);
            }
        }

        if ($totalPoints > 0) {
            $this->em->flush();
        }

        return $totalPoints;
    }

    /** @return array{members: int, pointsDistributed: int, pointsUsed: int, pointsExpired: int, rewardsRedeemed: int, programCostCents: int, topCustomers: list<array{customerId: string, totalEarned: int, pointsBalance: int}>} */
    public function getDashboardStats(Boutique $boutique, ?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $topCustomers = array_map(
            static fn (CustomerLoyalty $cl) => [
                'customerId' => (string) $cl->getCustomer()->getId(),
                'totalEarned' => $cl->getTotalEarned(),
                'pointsBalance' => $cl->getPointsBalance(),
            ],
            $this->customerLoyalties->findTopCustomers($boutique, 10),
        );

        return [
            'members' => $this->customerLoyalties->countMembers($boutique),
            'pointsDistributed' => $this->transactions->sumPointsByType($boutique, LoyaltyTransactionType::Earn, $from, $to),
            'pointsUsed' => $this->transactions->sumPointsByType($boutique, LoyaltyTransactionType::Redeem, $from, $to),
            'pointsExpired' => $this->transactions->sumPointsByType($boutique, LoyaltyTransactionType::Expiration, $from, $to),
            'rewardsRedeemed' => $this->transactions->countRewardsRedeemed($boutique, $from, $to),
            'programCostCents' => $this->transactions->sumDiscountCents($boutique, $from, $to),
            'topCustomers' => $topCustomers,
        ];
    }

    private function calculateRewardRedemption(LoyaltyProgram $program, CustomerLoyalty $account, string $rewardId, LoyaltyEvaluationContext $context): LoyaltyCalculationResult
    {
        if (!$program->isAllowRewardSelection()) {
            return LoyaltyCalculationResult::failure('La sélection de récompense n\'est pas autorisée pour cette boutique.');
        }

        $reward = $this->rewards->find($rewardId);
        if (!$reward instanceof LoyaltyReward || (string) $reward->getProgram()->getId() !== (string) $program->getId() || !$reward->isActive()) {
            return LoyaltyCalculationResult::failure('Récompense introuvable ou inactive.');
        }

        if (null !== $reward->getMinOrderAmountCents() && $context->subtotalCents < $reward->getMinOrderAmountCents()) {
            return LoyaltyCalculationResult::failure('Montant de commande insuffisant pour cette récompense.');
        }

        $orderCount = $this->orders->countValidByCustomer($context->customer);
        if (null !== $reward->getMinOrdersRequired() && $orderCount < $reward->getMinOrdersRequired()) {
            return LoyaltyCalculationResult::failure('Nombre de commandes insuffisant pour cette récompense.');
        }

        if (null !== $reward->getUsageLimit() && $this->transactions->countRewardUsage($reward) >= $reward->getUsageLimit()) {
            return LoyaltyCalculationResult::failure("Cette récompense n'est plus disponible (limite atteinte).");
        }

        if (null !== $reward->getUsageLimitPerCustomer() && $this->transactions->countRewardUsageByCustomer($reward, $account) >= $reward->getUsageLimitPerCustomer()) {
            return LoyaltyCalculationResult::failure('Vous avez déjà utilisé cette récompense le nombre maximum de fois.');
        }

        $cost = $reward->getCostValue();
        if (LoyaltyCostType::Points === $reward->getCostType()) {
            if ($account->getPointsBalance() < $cost) {
                return LoyaltyCalculationResult::failure('Solde de points insuffisant pour cette récompense.');
            }
        } elseif ($orderCount < $cost) {
            return LoyaltyCalculationResult::failure('Nombre de commandes insuffisant pour cette récompense.');
        }

        if (!$this->isCombinabilityRespected($program, $reward, $context)) {
            return LoyaltyCalculationResult::failure('Cette récompense ne peut pas être combinée avec les remises déjà appliquées.');
        }

        $applier = $this->rewardApplierRegistry->find($reward->getTypeCode());
        if (null === $applier) {
            return LoyaltyCalculationResult::failure('Type de récompense non supporté.');
        }

        $application = $applier->apply($reward, $context);
        $discountCents = $application->discountCents;
        if (null !== $program->getMaxDiscountCentsPerOrder()) {
            $discountCents = min($discountCents, $program->getMaxDiscountCentsPerOrder());
        }

        $pointsUsed = LoyaltyCostType::Points === $reward->getCostType() ? $cost : 0;

        return new LoyaltyCalculationResult(
            success: true,
            pointsUsed: $pointsUsed,
            discountCents: $discountCents,
            newSubtotalCents: max(0, $context->subtotalCents - $discountCents),
            freeShipping: $application->freeShipping,
            freeProductId: $application->freeProductId,
            rewardId: (string) $reward->getId(),
            metadata: $application->metadata,
        );
    }

    private function calculateGenericPointsRedemption(LoyaltyProgram $program, CustomerLoyalty $account, LoyaltyRedemptionRequest $request, LoyaltyEvaluationContext $context): LoyaltyCalculationResult
    {
        if ($request->useAllPoints && !$program->isAllowUseAllPoints()) {
            return LoyaltyCalculationResult::failure("L'utilisation de tous les points n'est pas autorisée.");
        }
        if (!$request->useAllPoints && !$program->isAllowChooseAmount()) {
            return LoyaltyCalculationResult::failure('Le choix du nombre de points à utiliser n\'est pas autorisé.');
        }

        $requestedPoints = $request->useAllPoints
            ? $account->getPointsBalance()
            : (int) ($request->pointsToUse ?? 0);

        if (null !== $program->getMaxPointsPerOrder()) {
            $requestedPoints = min($requestedPoints, $program->getMaxPointsPerOrder());
        }
        $requestedPoints = min($requestedPoints, $account->getPointsBalance());

        if ($requestedPoints < $program->getMinPointsToRedeem()) {
            return LoyaltyCalculationResult::failure(sprintf('Minimum %d points requis pour utiliser vos points.', $program->getMinPointsToRedeem()));
        }

        if ($requestedPoints <= 0) {
            return LoyaltyCalculationResult::failure('Aucun point à utiliser.');
        }

        $pointValueCents = max(1, $program->getPointValueCents());
        $discountCents = $requestedPoints * $pointValueCents;
        $discountCents = min($discountCents, $context->remainingSpendableCents());
        if (null !== $program->getMaxDiscountCentsPerOrder()) {
            $discountCents = min($discountCents, $program->getMaxDiscountCentsPerOrder());
        }

        // If the discount got capped, don't charge the customer for points beyond the value actually used.
        $effectivePoints = min($requestedPoints, intdiv($discountCents, $pointValueCents));
        $discountCents = $effectivePoints * $pointValueCents;

        if ($effectivePoints <= 0) {
            return LoyaltyCalculationResult::failure('Aucune remise applicable (plafond de remise atteint).');
        }

        return new LoyaltyCalculationResult(
            success: true,
            pointsUsed: $effectivePoints,
            discountCents: $discountCents,
            newSubtotalCents: max(0, $context->subtotalCents - $discountCents),
        );
    }

    private function isCombinabilityRespected(LoyaltyProgram $program, LoyaltyReward $reward, LoyaltyEvaluationContext $context): bool
    {
        if ($context->alreadyAppliedDiscountsCents <= 0) {
            return true;
        }

        $combinable = $reward->getCombinableWithOtherDiscounts() ?? $program->isCombinableWithOtherDiscounts();

        return $combinable;
    }

    /** @param list<LoyaltyRule> $multiplierRules
     * @param list<LoyaltyRule> $firedRules */
    private function resolveMultiplier(array $multiplierRules, array $firedRules, \DateTimeImmutable $now): float
    {
        $firedCodes = array_map(static fn (LoyaltyRule $r) => $r->getTriggerCode(), $firedRules);
        $multiplier = 1.0;

        foreach ($multiplierRules as $multiplierRule) {
            if (!$multiplierRule->isCurrentlyActive($now)) {
                continue;
            }

            $appliesTo = $multiplierRule->getAppliesToTriggerCodes();
            $applies = null === $appliesTo || [] === $appliesTo || [] !== array_intersect($appliesTo, $firedCodes);

            if ($applies) {
                $multiplier = max($multiplier, $multiplierRule->getMultiplierValue());
            }
        }

        return $multiplier;
    }

    private function unlockRewardIfAny(LoyaltyRule $rule, CustomerLoyalty $account): void
    {
        $reward = $rule->getUnlockedReward();
        if (null === $reward) {
            return;
        }

        $this->notifications->create(
            $account->getCustomer(),
            'loyalty_reward_unlocked',
            'Récompense débloquée !',
            sprintf('Vous avez débloqué la récompense "%s".', $reward->getName()),
        );
    }

    private function consumeFifo(CustomerLoyalty $account, int $amount): void
    {
        $remaining = $amount;
        foreach ($this->transactions->findConsumableEarnBatches($account) as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $remaining -= $batch->consumePoints($remaining);
        }
    }

    private function computeExpiry(LoyaltyProgram $program, \DateTimeImmutable $from): ?\DateTimeImmutable
    {
        $days = $program->getValidityDays();

        return null !== $days ? $from->modify(sprintf('+%d days', $days)) : null;
    }

    private function buildContext(
        Boutique $boutique,
        ?Customer $customer,
        ?CustomerLoyalty $account,
        ?Order $order,
        int $subtotalCents = 0,
        int $alreadyAppliedDiscountsCents = 0,
        array $extra = [],
    ): LoyaltyEvaluationContext {
        $orderCount = null !== $customer ? $this->orders->countValidByCustomer($customer) : 0;

        return new LoyaltyEvaluationContext(
            boutique: $boutique,
            customer: $customer,
            customerLoyalty: $account,
            order: $order,
            subtotalCents: $subtotalCents,
            alreadyAppliedDiscountsCents: $alreadyAppliedDiscountsCents,
            customerOrderCount: $orderCount,
            extra: $extra,
        );
    }
}
