<?php

namespace App\ApiResource\Cart;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Cart\CartCheckoutInput;
use App\Dto\Cart\CartCheckoutOutput;
use App\Dto\Cart\CartItemInput;
use App\Dto\Cart\CartItemQuantityInput;
use App\Dto\Cart\CartOutput;
use App\State\Cart\CartProcessor;
use App\State\Cart\CartProvider;

#[ApiResource(
    shortName: 'Cart',
    operations: [
        new Get(
            uriTemplate: '/cart',
            security: "is_granted('PUBLIC_ACCESS')",
            output: CartOutput::class,
            provider: CartProvider::class,
        ),
        new Post(
            name: 'cart_add_item',
            uriTemplate: '/cart/items',
            security: "is_granted('PUBLIC_ACCESS')",
            input: CartItemInput::class,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Patch(
            name: 'cart_update_item',
            uriTemplate: '/cart/items/{itemId}',
            uriVariables: ['itemId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            input: CartItemQuantityInput::class,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Delete(
            name: 'cart_remove_item',
            uriTemplate: '/cart/items/{itemId}',
            uriVariables: ['itemId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            read: false,
            input: false,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Post(
            name: 'cart_checkout',
            uriTemplate: '/cart/checkout',
            security: "is_granted('PUBLIC_ACCESS')",
            input: CartCheckoutInput::class,
            output: CartCheckoutOutput::class,
            processor: CartProcessor::class,
        ),
    ],
)]
final class CartResource
{
    public ?string $id = null;
}
