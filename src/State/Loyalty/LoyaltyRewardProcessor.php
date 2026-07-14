<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Loyalty\LoyaltyRewardInput;
use App\Dto\Loyalty\LoyaltyRewardOutput;
use App\Entity\LoyaltyReward;
use App\Enum\LoyaltyCostType;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyCacheService;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<LoyaltyRewardOutput|null> */
final readonly class LoyaltyRewardProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private EntityManagerInterface $em,
        private LoyaltyEngine $engine,
        private LoyaltyCacheService $cache,
        private LoyaltyRewardProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyRewardOutput
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        if ($operation instanceof Delete) {
            $reward = $this->provider->findReward($boutique, (string) ($uriVariables['id'] ?? ''));
            if (!$reward instanceof LoyaltyReward) {
                throw new NotFoundHttpException('Récompense de fidélité introuvable');
            }
            $this->em->remove($reward);
            $this->em->flush();
            $this->cache->invalidateRewards((string) $boutique->getId());

            return null;
        }

        assert($data instanceof LoyaltyRewardInput);
        $program = $this->engine->getOrCreateProgram($boutique);

        $reward = isset($uriVariables['id']) ? $this->provider->findReward($boutique, (string) $uriVariables['id']) : null;
        if (isset($uriVariables['id']) && !$reward instanceof LoyaltyReward) {
            throw new NotFoundHttpException('Récompense de fidélité introuvable');
        }

        if (!$reward instanceof LoyaltyReward) {
            $reward = new LoyaltyReward(program: $program, name: $data->name, typeCode: $data->typeCode);
            $this->em->persist($reward);
        }

        $this->applyFields($reward, $data);

        $this->em->flush();
        $this->cache->invalidateRewards((string) $boutique->getId());

        return $this->provider->toOutput($reward);
    }

    private function applyFields(LoyaltyReward $reward, LoyaltyRewardInput $d): void
    {
        $reward->setName($d->name);
        $reward->setDescription($d->description);
        $reward->setTypeCode($d->typeCode);
        $reward->setConfig($d->config);
        $reward->setCostType(LoyaltyCostType::tryFrom($d->costType) ?? LoyaltyCostType::Points);
        $reward->setCostValue($d->costValue);
        $reward->setMinOrderAmountCents($d->minOrderAmountCents);
        $reward->setMaxDiscountCents($d->maxDiscountCents);
        $reward->setMinOrdersRequired($d->minOrdersRequired);
        $reward->setValidityDays($d->validityDays);
        $reward->setCombinableWithPromotions($d->combinableWithPromotions);
        $reward->setCombinableWithCoupons($d->combinableWithCoupons);
        $reward->setCombinableWithOtherDiscounts($d->combinableWithOtherDiscounts);
        $reward->setCombinableWithFreeShipping($d->combinableWithFreeShipping);
        $reward->setUsageLimit($d->usageLimit);
        $reward->setUsageLimitPerCustomer($d->usageLimitPerCustomer);
        $reward->setPriority($d->priority);
        $reward->setActive($d->isActive);
    }
}
