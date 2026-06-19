<?php

namespace App\ApiResource\Invoice;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Invoice\InvoiceOutput;
use App\State\Invoice\InvoiceProcessor;
use App\State\Invoice\InvoiceProvider;

#[ApiResource(
    shortName: 'Invoice',
    operations: [
        new GetCollection(uriTemplate: '/invoices', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", output: InvoiceOutput::class, provider: InvoiceProvider::class),
        new Get(uriTemplate: '/invoices/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", output: InvoiceOutput::class, provider: InvoiceProvider::class),
        new Post(name: 'generate_order_invoice', uriTemplate: '/orders/{orderId}/invoice', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", input: false, output: InvoiceOutput::class, processor: InvoiceProcessor::class),
        new GetCollection(uriTemplate: '/admin/invoices', security: "is_granted('ROLE_SUPER_ADMIN')", output: InvoiceOutput::class, provider: InvoiceProvider::class),
        new Get(uriTemplate: '/admin/invoices/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", output: InvoiceOutput::class, provider: InvoiceProvider::class),
        new Post(name: 'generate_subscription_invoice', uriTemplate: '/admin/subscriptions/{subscriptionId}/invoice', security: "is_granted('ROLE_SUPER_ADMIN')", input: false, output: InvoiceOutput::class, processor: InvoiceProcessor::class),
        new Patch(name: 'mark_invoice_paid', uriTemplate: '/admin/invoices/{id}/mark-paid', security: "is_granted('ROLE_SUPER_ADMIN')", input: false, output: InvoiceOutput::class, processor: InvoiceProcessor::class),
    ],
)]
final class InvoiceResource
{
    public ?string $id = null;
}
