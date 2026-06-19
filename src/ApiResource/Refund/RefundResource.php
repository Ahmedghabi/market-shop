<?php

namespace App\ApiResource\Refund;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\State\Refund\RefundProvider;
use App\State\Refund\RefundProcessor;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/admin/refunds/{id}',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: RefundProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/refunds',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            provider: RefundProvider::class,
        ),
        new Post(
            uriTemplate: '/refunds',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: RefundProcessor::class,
        ),
        new Post(
            uriTemplate: '/admin/refunds/{id}/approve',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: RefundProcessor::class,
            name: 'refund_approve',
        ),
        new Post(
            uriTemplate: '/admin/refunds/{id}/process',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: RefundProcessor::class,
            name: 'refund_process',
        ),
        new Post(
            uriTemplate: '/admin/refunds/{id}/reject',
            security: "is_granted('ROLE_BOUTIQUE_ADMIN')",
            processor: RefundProcessor::class,
            name: 'refund_reject',
        ),
    ],
)]
final class RefundResource
{
}
