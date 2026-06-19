<?php

namespace App\State\Coupon;

use App\Dto\Coupon\CouponInput;
use App\Service\Marketing\CouponService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final class CouponProcessor implements ProcessorInterface
{
    public function __construct(
        private CouponService $couponService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? '';

        if ($data instanceof CouponInput) {
            if (isset($uriVariables['id'])) {
                $coupon = $this->couponService->getCouponById($uriVariables['id']);
                if ($coupon) {
                    return $this->couponService->update($coupon, (array) $data);
                }
            }

            return $this->couponService->create($boutiqueId, (array) $data);
        }

        return null;
    }
}
