<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Cart\CartOutput;
use App\Service\Cart\CartService;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<CartOutput> */
final readonly class CartProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(private CartService $carts)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CartOutput
    {
        unset($operation);

        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique) {
            return $this->carts->currentCart('');
        }

        return $this->carts->currentCart((string) $boutique->getId());
    }
}
