<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Cart\CartOutput;
use App\Service\Cart\CartService;

/** @implements ProviderInterface<CartOutput> */
final readonly class CartProvider implements ProviderInterface
{
    public function __construct(private CartService $carts)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CartOutput
    {
        unset($operation, $context);

        return $this->carts->currentCart((string) ($uriVariables['boutiqueId'] ?? ''));
    }
}
