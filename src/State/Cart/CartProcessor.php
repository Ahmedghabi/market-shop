<?php

namespace App\State\Cart;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Cart\CartCheckoutInput;
use App\Dto\Cart\CartCheckoutOutput;
use App\Dto\Cart\CartItemInput;
use App\Dto\Cart\CartItemQuantityInput;
use App\Dto\Cart\CartOutput;
use App\Service\Cart\CartService;

/** @implements ProcessorInterface<object, CartOutput|CartCheckoutOutput> */
final readonly class CartProcessor implements ProcessorInterface
{
    public function __construct(private CartService $carts)
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CartOutput|CartCheckoutOutput
    {
        unset($context);

        $boutiqueId = (string) ($uriVariables['boutiqueId'] ?? '');
        $operationName = $operation->getName() ?? '';

        if ('cart_add_item' === $operationName && $data instanceof CartItemInput) {
            return $this->carts->addItem($boutiqueId, $data->productId, $data->quantity);
        }

        if ('cart_update_item' === $operationName && $data instanceof CartItemQuantityInput) {
            return $this->carts->updateItem($boutiqueId, (string) ($uriVariables['itemId'] ?? ''), $data->quantity);
        }

        if ('cart_remove_item' === $operationName) {
            return $this->carts->removeItem($boutiqueId, (string) ($uriVariables['itemId'] ?? ''));
        }

        if ('cart_checkout' === $operationName && $data instanceof CartCheckoutInput) {
            return $this->carts->checkout($boutiqueId, $data);
        }

        throw new \InvalidArgumentException('Unsupported cart operation.');
    }
}
