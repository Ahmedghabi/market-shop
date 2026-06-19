<?php

namespace App\State\Coupon;

use App\Dto\Coupon\CouponOutput;
use App\Entity\Coupon;
use App\Repository\CouponRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\State\Common\BoutiqueAwareProviderTrait;

final class CouponProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private CouponRepository $coupons,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?CouponOutput
    {
        $coupon = $this->coupons->find($uriVariables['id'] ?? null);
        if (!$coupon instanceof Coupon) {
            return null;
        }

        return $this->toOutput($coupon);
    }

    /** @return list<CouponOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique) {
            return [];
        }

        $coupons = $this->coupons->findByBoutique($boutique);

        return array_map($this->toOutput(...), $coupons);
    }

    private function toOutput(Coupon $coupon): CouponOutput
    {
        return new CouponOutput(
            id: (string) $coupon->getId(),
            code: $coupon->getCode(),
            name: $coupon->getName(),
            type: $coupon->getType()->value,
            scope: $coupon->getScope()->value,
            value: $coupon->getValue(),
            maxDiscountCents: $coupon->getMaxDiscountCents(),
            minCartAmountCents: $coupon->getMinCartAmountCents(),
            maxCartAmountCents: $coupon->getMaxCartAmountCents(),
            usageLimit: $coupon->getUsageLimit(),
            usedCount: $coupon->getUsedCount(),
            perUserLimit: $coupon->getPerUserLimit(),
            combineWithPromotions: $coupon->isCombineWithPromotions(),
            isActive: $coupon->isActive(),
            startsAt: $coupon->getStartsAt()?->format('c'),
            expiresAt: $coupon->getExpiresAt()?->format('c'),
            buyXGetYConfig: $coupon->getBuyXGetYConfig(),
            createdAt: $coupon->getCreatedAt()->format('c'),
            updatedAt: $coupon->getUpdatedAt()?->format('c'),
        );
    }
}
