<?php

namespace App\Service\Marketing;

use App\Entity\Coupon;
use App\Entity\CouponProduct;
use App\Entity\CouponCategory;
use App\Enum\CouponScope;
use App\Enum\CouponType;
use App\Repository\CouponRepository;
use App\Repository\CouponProductRepository;
use App\Repository\CouponCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CouponService
{
    public function __construct(
        private CouponRepository $coupons,
        private CouponProductRepository $couponProducts,
        private CouponCategoryRepository $couponCategories,
        private EntityManagerInterface $em,
        private CouponCacheService $cache,
    ) {
    }

    public function validate(string $boutiqueId, string $code): Coupon
    {
        $coupon = $this->coupons->findActiveByCode($boutiqueId, $code);
        if (!$coupon instanceof Coupon) {
            throw new NotFoundHttpException('Coupon introuvable.');
        }

        if (!$coupon->isCurrentlyValid()) {
            throw new \DomainException('Ce coupon n\'est plus valide.');
        }

        return $coupon;
    }

    public function apply(Coupon $coupon, int $cartAmountCents): int
    {
        $discount = 0;

        return match ($coupon->getType()) {
            CouponType::Percent => (int) round($cartAmountCents * $coupon->getValue() / 100),
            CouponType::FixedAmount => min($coupon->getValue(), $cartAmountCents),
            CouponType::FreeShipping => 0,
            CouponType::BuyXGetY => 0,
        };
    }

    public function getMaxDiscount(Coupon $coupon, int $cartAmountCents): int
    {
        $discount = $this->apply($coupon, $cartAmountCents);

        if (null !== $coupon->getMaxDiscountCents()) {
            return min($discount, $coupon->getMaxDiscountCents());
        }

        return $discount;
    }

    public function create(string $boutiqueId, array $data): Coupon
    {
        $boutique = $this->em->find(\App\Entity\Boutique::class, $boutiqueId);
        if (!$boutique instanceof \App\Entity\Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        $coupon = new Coupon(
            boutique: $boutique,
            code: strtoupper(trim((string) ($data['code'] ?? ''))),
            name: (string) ($data['name'] ?? ''),
            type: CouponType::from((string) ($data['type'] ?? 'PERCENT')),
            scope: CouponScope::from((string) ($data['scope'] ?? 'GLOBAL')),
            value: (int) ($data['value'] ?? 0),
        );

        if (isset($data['maxDiscountCents'])) {
            $coupon->setMaxDiscountCents((int) $data['maxDiscountCents']);
        }
        if (isset($data['minCartAmountCents'])) {
            $coupon->setMinCartAmountCents((int) $data['minCartAmountCents']);
        }
        if (isset($data['maxCartAmountCents'])) {
            $coupon->setMaxCartAmountCents((int) $data['maxCartAmountCents']);
        }
        if (isset($data['usageLimit'])) {
            $coupon->setUsageLimit((int) $data['usageLimit']);
        }
        if (isset($data['perUserLimit'])) {
            $coupon->setPerUserLimit((int) $data['perUserLimit']);
        }
        if (isset($data['combineWithPromotions'])) {
            $coupon->setCombineWithPromotions((bool) $data['combineWithPromotions']);
        }
        if (isset($data['isActive'])) {
            $coupon->setActive((bool) $data['isActive']);
        }
        if (isset($data['startsAt'])) {
            $coupon->setStartsAt(new \DateTimeImmutable((string) $data['startsAt']));
        }
        if (isset($data['expiresAt'])) {
            $coupon->setExpiresAt(new \DateTimeImmutable((string) $data['expiresAt']));
        }
        if (isset($data['buyXGetYConfig'])) {
            $coupon->setBuyXGetYConfig($data['buyXGetYConfig']);
        }

        $this->em->persist($coupon);

        if (CouponScope::Product === $coupon->getScope() && isset($data['productIds'])) {
            foreach ($data['productIds'] as $productId) {
                $product = $this->em->find(\App\Entity\Product::class, $productId);
                if ($product && (string) $product->getBoutique()->getId() === $boutiqueId) {
                    $cp = new CouponProduct($coupon, $product);
                    $this->em->persist($cp);
                    $coupon->addProduct($cp);
                }
            }
        }

        if (CouponScope::Category === $coupon->getScope() && isset($data['categoryIds'])) {
            foreach ($data['categoryIds'] as $categoryId) {
                $category = $this->em->find(\App\Entity\Category::class, $categoryId);
                if ($category && (string) $category->getBoutique()->getId() === $boutiqueId) {
                    $cc = new CouponCategory($coupon, $category);
                    $this->em->persist($cc);
                    $coupon->addCategory($cc);
                }
            }
        }

        $this->em->flush();
        $this->cache->invalidateShop($boutiqueId);

        return $coupon;
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        if (isset($data['name'])) {
            $coupon->setName((string) $data['name']);
        }
        if (isset($data['type'])) {
            $coupon->setType(CouponType::from((string) $data['type']));
        }
        if (isset($data['scope'])) {
            $coupon->setScope(CouponScope::from((string) $data['scope']));
        }
        if (isset($data['value'])) {
            $coupon->setValue((int) $data['value']);
        }
        if (array_key_exists('maxDiscountCents', $data)) {
            $coupon->setMaxDiscountCents(null !== $data['maxDiscountCents'] ? (int) $data['maxDiscountCents'] : null);
        }
        if (isset($data['minCartAmountCents'])) {
            $coupon->setMinCartAmountCents((int) $data['minCartAmountCents']);
        }
        if (isset($data['maxCartAmountCents'])) {
            $coupon->setMaxCartAmountCents((int) $data['maxCartAmountCents']);
        }
        if (isset($data['usageLimit'])) {
            $coupon->setUsageLimit((int) $data['usageLimit']);
        }
        if (isset($data['perUserLimit'])) {
            $coupon->setPerUserLimit((int) $data['perUserLimit']);
        }
        if (isset($data['combineWithPromotions'])) {
            $coupon->setCombineWithPromotions((bool) $data['combineWithPromotions']);
        }
        if (isset($data['isActive'])) {
            $coupon->setActive((bool) $data['isActive']);
        }
        if (isset($data['startsAt'])) {
            $coupon->setStartsAt(new \DateTimeImmutable((string) $data['startsAt']));
        }
        if (isset($data['expiresAt'])) {
            $coupon->setExpiresAt(new \DateTimeImmutable((string) $data['expiresAt']));
        }
        if (isset($data['buyXGetYConfig'])) {
            $coupon->setBuyXGetYConfig($data['buyXGetYConfig']);
        }

        $this->em->flush();
        $this->cache->invalidateShop((string) $coupon->getBoutique()->getId());

        return $coupon;
    }

    public function getCouponsForBoutique(string $boutiqueId): array
    {
        return $this->coupons->findByBoutique($boutiqueId);
    }

    public function getCouponById(string $couponId): ?Coupon
    {
        return $this->coupons->find($couponId);
    }

    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->incrementUsedCount();
        $this->em->flush();
    }
}
