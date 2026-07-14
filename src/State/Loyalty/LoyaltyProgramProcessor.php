<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Loyalty\LoyaltyProgramInput;
use App\Dto\Loyalty\LoyaltyProgramOutput;
use App\Entity\LoyaltyProgram;
use App\Enum\LoyaltyValidityPolicy;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyCacheService;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @implements ProcessorInterface<LoyaltyProgramOutput> */
final readonly class LoyaltyProgramProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private LoyaltyEngine $engine,
        private EntityManagerInterface $em,
        private LoyaltyCacheService $cache,
        private LoyaltyProgramProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyProgramOutput
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);
        $program = $this->engine->getOrCreateProgram($boutique);

        assert($data instanceof LoyaltyProgramInput);
        $this->applyFields($program, $data);

        $this->em->flush();
        $this->cache->invalidateProgram((string) $boutique->getId());

        return $this->provider->toOutput($program);
    }

    private function applyFields(LoyaltyProgram $program, LoyaltyProgramInput $d): void
    {
        if (null !== $d->isActive) {
            $program->setActive($d->isActive);
        }
        if (null !== $d->pointsValidityPolicy) {
            $program->setPointsValidityPolicy(LoyaltyValidityPolicy::tryFrom($d->pointsValidityPolicy) ?? $program->getPointsValidityPolicy());
        }
        if (null !== $d->customValidityDays) {
            $program->setCustomValidityDays($d->customValidityDays);
        }
        if (null !== $d->pointValueCents) {
            $program->setPointValueCents($d->pointValueCents);
        }
        if (null !== $d->allowChooseAmount) {
            $program->setAllowChooseAmount($d->allowChooseAmount);
        }
        if (null !== $d->allowUseAllPoints) {
            $program->setAllowUseAllPoints($d->allowUseAllPoints);
        }
        if (null !== $d->allowRewardSelection) {
            $program->setAllowRewardSelection($d->allowRewardSelection);
        }
        if (null !== $d->minPointsToRedeem) {
            $program->setMinPointsToRedeem($d->minPointsToRedeem);
        }
        if (null !== $d->maxPointsPerOrder) {
            $program->setMaxPointsPerOrder($d->maxPointsPerOrder);
        }
        if (null !== $d->maxDiscountCentsPerOrder) {
            $program->setMaxDiscountCentsPerOrder($d->maxDiscountCentsPerOrder);
        }
        if (null !== $d->minOrderAmountCentsToRedeem) {
            $program->setMinOrderAmountCentsToRedeem($d->minOrderAmountCentsToRedeem);
        }
        if (null !== $d->minOrdersCountToRedeem) {
            $program->setMinOrdersCountToRedeem($d->minOrdersCountToRedeem);
        }
        if (null !== $d->combinableWithPromotions) {
            $program->setCombinableWithPromotions($d->combinableWithPromotions);
        }
        if (null !== $d->combinableWithCoupons) {
            $program->setCombinableWithCoupons($d->combinableWithCoupons);
        }
        if (null !== $d->combinableWithOtherDiscounts) {
            $program->setCombinableWithOtherDiscounts($d->combinableWithOtherDiscounts);
        }
        if (null !== $d->combinableWithFreeShipping) {
            $program->setCombinableWithFreeShipping($d->combinableWithFreeShipping);
        }
        if (null !== $d->returnUsedPointsOnCancel) {
            $program->setReturnUsedPointsOnCancel($d->returnUsedPointsOnCancel);
        }
        if (null !== $d->revokeEarnedPointsOnCancel) {
            $program->setRevokeEarnedPointsOnCancel($d->revokeEarnedPointsOnCancel);
        }
        if (null !== $d->calculationOrder) {
            $program->setCalculationOrder($d->calculationOrder);
        }
        if (null !== $d->cacheTtlSeconds) {
            $program->setCacheTtlSeconds($d->cacheTtlSeconds);
        }
    }
}
