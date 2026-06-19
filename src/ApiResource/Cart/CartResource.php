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

const BOUTIQUE_CART_URI_VARIABLES = [
    'boutiqueId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id'),
];

#[ApiResource(
    shortName: 'Cart',
    operations: [
        new Get(
            uriTemplate: '/boutiques/{boutiqueId}/cart',
            uriVariables: BOUTIQUE_CART_URI_VARIABLES,
            security: "is_granted('PUBLIC_ACCESS')",
            output: CartOutput::class,
            provider: CartProvider::class,
        ),
        new Post(
            name: 'cart_add_item',
            uriTemplate: '/boutiques/{boutiqueId}/cart/items',
            uriVariables: BOUTIQUE_CART_URI_VARIABLES,
            security: "is_granted('PUBLIC_ACCESS')",
            input: CartItemInput::class,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Patch(
            name: 'cart_update_item',
            uriTemplate: '/boutiques/{boutiqueId}/cart/items/{itemId}',
            uriVariables: BOUTIQUE_CART_URI_VARIABLES + ['itemId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            input: CartItemQuantityInput::class,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Delete(
            name: 'cart_remove_item',
            uriTemplate: '/boutiques/{boutiqueId}/cart/items/{itemId}',
            uriVariables: BOUTIQUE_CART_URI_VARIABLES + ['itemId' => new Link(schema: ['type' => 'string', 'format' => 'uuid'], property: 'id')],
            security: "is_granted('PUBLIC_ACCESS')",
            input: false,
            output: CartOutput::class,
            processor: CartProcessor::class,
        ),
        new Post(
            name: 'cart_checkout',
            uriTemplate: '/boutiques/{boutiqueId}/cart/checkout',
            uriVariables: BOUTIQUE_CART_URI_VARIABLES,
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
