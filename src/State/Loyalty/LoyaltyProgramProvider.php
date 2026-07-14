<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyProgramOutput;
use App\Entity\LoyaltyProgram;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<LoyaltyProgramOutput> */
final readonly class LoyaltyProgramProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private LoyaltyEngine $engine,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyProgramOutput
    {
        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (null === $boutique || !$this->context->canAccessBoutique($boutique)) {
            return null;
        }

        return $this->toOutput($this->engine->getOrCreateProgram($boutique));
    }

    public function toOutput(LoyaltyProgram $program): LoyaltyProgramOutput
    {
        $output = new LoyaltyProgramOutput();
        $output->id = (string) $program->getId();
        $output->boutiqueId = (string) $program->getBoutique()->getId();
        $output->isActive = $program->isActive();
        $output->pointsValidityPolicy = $program->getPointsValidityPolicy()->value;
        $output->customValidityDays = $program->getCustomValidityDays();
        $output->validityDays = $program->getValidityDays();
        $output->pointValueCents = $program->getPointValueCents();
        $output->allowChooseAmount = $program->isAllowChooseAmount();
        $output->allowUseAllPoints = $program->isAllowUseAllPoints();
        $output->allowRewardSelection = $program->isAllowRewardSelection();
        $output->minPointsToRedeem = $program->getMinPointsToRedeem();
        $output->maxPointsPerOrder = $program->getMaxPointsPerOrder();
        $output->maxDiscountCentsPerOrder = $program->getMaxDiscountCentsPerOrder();
        $output->minOrderAmountCentsToRedeem = $program->getMinOrderAmountCentsToRedeem();
        $output->minOrdersCountToRedeem = $program->getMinOrdersCountToRedeem();
        $output->combinableWithPromotions = $program->isCombinableWithPromotions();
        $output->combinableWithCoupons = $program->isCombinableWithCoupons();
        $output->combinableWithOtherDiscounts = $program->isCombinableWithOtherDiscounts();
        $output->combinableWithFreeShipping = $program->isCombinableWithFreeShipping();
        $output->returnUsedPointsOnCancel = $program->isReturnUsedPointsOnCancel();
        $output->revokeEarnedPointsOnCancel = $program->isRevokeEarnedPointsOnCancel();
        $output->calculationOrder = $program->getCalculationOrder();
        $output->cacheTtlSeconds = $program->getCacheTtlSeconds();
        $output->createdAt = $program->getCreatedAt();
        $output->updatedAt = $program->getUpdatedAt();

        return $output;
    }
}
