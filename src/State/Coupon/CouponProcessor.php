<?php

namespace App\State\Coupon;

use App\Dto\Coupon\CouponInput;
use App\Service\Marketing\CouponService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Boutique;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CouponProcessor implements ProcessorInterface
{
    public function __construct(
        private CouponService $couponService,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $boutiqueContext,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $boutique = $this->resolveBoutique($context, $uriVariables);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        $boutiqueId = (string) $boutique->getId();

        if ($data instanceof CouponInput) {
            if (isset($uriVariables['id'])) {
                $coupon = $this->couponService->getCouponById($uriVariables['id']);
                if ($coupon && (string) $coupon->getBoutique()->getId() === $boutiqueId) {
                    return $this->couponService->update($coupon, (array) $data);
                }
                throw new NotFoundHttpException('Coupon not found');
            }

            return $this->couponService->create($boutiqueId, (array) $data);
        }

        return null;
    }

    private function resolveBoutique(array $context, array $uriVariables): ?Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof Request ? $request->attributes->get('_boutique') : null;
        if ($boutique instanceof Boutique) {
            return $boutique;
        }

        $id = $uriVariables['boutiqueId'] ?? $this->boutiqueContext->getBoutiqueId();

        return null !== $id ? $this->boutiques->findBySlugOrId((string) $id) : null;
    }
}
