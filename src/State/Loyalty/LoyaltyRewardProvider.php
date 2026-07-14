<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyRewardOutput;
use App\Entity\Boutique;
use App\Entity\LoyaltyReward;
use App\Repository\BoutiqueRepository;
use App\Repository\LoyaltyRewardRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<LoyaltyRewardOutput> */
final readonly class LoyaltyRewardProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private LoyaltyRewardRepository $rewards,
        private BoutiqueContext $context,
        private LoyaltyEngine $engine,
    ) {
    }

    /** @return list<LoyaltyRewardOutput>|LoyaltyRewardOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|LoyaltyRewardOutput|null
    {
        // Not just Get: Patch/Delete/Put also read a single item first (see ReadProvider).
        $isItemOperation = !$operation instanceof GetCollection && isset($uriVariables['id']);

        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (null === $boutique || !$this->context->canAccessBoutique($boutique)) {
            return $isItemOperation ? null : [];
        }

        if ($isItemOperation) {
            $reward = $this->findReward($boutique, (string) ($uriVariables['id'] ?? ''));

            return $reward instanceof LoyaltyReward ? $this->toOutput($reward) : null;
        }

        $program = $this->engine->getOrCreateProgram($boutique);

        return array_map($this->toOutput(...), $this->rewards->findByProgram($program));
    }

    public function findReward(Boutique $boutique, string $id): ?LoyaltyReward
    {
        $reward = $this->rewards->find($id);

        return $reward instanceof LoyaltyReward && (string) $reward->getProgram()->getBoutique()->getId() === (string) $boutique->getId() ? $reward : null;
    }

    public function toOutput(LoyaltyReward $reward): LoyaltyRewardOutput
    {
        $output = new LoyaltyRewardOutput();
        $output->id = (string) $reward->getId();
        $output->programId = (string) $reward->getProgram()->getId();
        $output->name = $reward->getName();
        $output->description = $reward->getDescription();
        $output->typeCode = $reward->getTypeCode();
        $output->config = $reward->getConfig();
        $output->costType = $reward->getCostType()->value;
        $output->costValue = $reward->getCostValue();
        $output->minOrderAmountCents = $reward->getMinOrderAmountCents();
        $output->maxDiscountCents = $reward->getMaxDiscountCents();
        $output->minOrdersRequired = $reward->getMinOrdersRequired();
        $output->validityDays = $reward->getValidityDays();
        $output->combinableWithPromotions = $reward->getCombinableWithPromotions();
        $output->combinableWithCoupons = $reward->getCombinableWithCoupons();
        $output->combinableWithOtherDiscounts = $reward->getCombinableWithOtherDiscounts();
        $output->combinableWithFreeShipping = $reward->getCombinableWithFreeShipping();
        $output->usageLimit = $reward->getUsageLimit();
        $output->usageLimitPerCustomer = $reward->getUsageLimitPerCustomer();
        $output->priority = $reward->getPriority();
        $output->isActive = $reward->isActive();
        $output->createdAt = $reward->getCreatedAt();
        $output->updatedAt = $reward->getUpdatedAt();

        return $output;
    }
}
