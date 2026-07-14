<?php

namespace App\Tests\Service\Marketing;

use App\Entity\Coupon;
use App\Enum\CouponScope;
use App\Enum\CouponType;
use App\Service\Marketing\CouponService;
use PHPUnit\Framework\TestCase;

final class CouponServiceTest extends TestCase
{
    /** @dataProvider discountProvider */
    public function testApplyCalculatesDiscount(CouponType $type, int $value, int $cart, int $expected): void
    {
        $coupon = new Coupon(
            boutique: $this->createMock(\App\Entity\Boutique::class),
            code: 'TEST',
            name: 'Test coupon',
            type: $type,
            scope: CouponScope::Global,
            value: $value,
        );

        self::assertSame($expected, $this->service()->apply($coupon, $cart));
    }

    public static function discountProvider(): iterable
    {
        yield 'percentage' => [CouponType::Percent, 20, 10000, 2000];
        yield 'fixed amount capped by cart' => [CouponType::FixedAmount, 15000, 10000, 10000];
        yield 'free shipping' => [CouponType::FreeShipping, 100, 10000, 0];
        yield 'buy x get y' => [CouponType::BuyXGetY, 1, 10000, 0];
    }

    public function testMaxDiscountCapsPercentageDiscount(): void
    {
        $coupon = new Coupon(
            boutique: $this->createMock(\App\Entity\Boutique::class),
            code: 'TEST',
            name: 'Test coupon',
            type: CouponType::Percent,
            value: 50,
            maxDiscountCents: 1000,
        );

        self::assertSame(1000, $this->service()->getMaxDiscount($coupon, 10000));
    }

    private function service(): CouponService
    {
        /** @var CouponService $service */
        $service = (new \ReflectionClass(CouponService::class))->newInstanceWithoutConstructor();

        return $service;
    }
}
