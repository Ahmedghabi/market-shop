<?php

namespace App\State\Invoice;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Invoice\InvoiceOutput;
use App\Service\Billing\InvoiceService;
use App\Service\Webhook\WebhookService;

/** @implements ProcessorInterface<InvoiceOutput|null> */
final readonly class InvoiceProcessor implements ProcessorInterface
{
    public function __construct(
        private InvoiceService $invoices,
        private InvoiceProvider $provider,
        private WebhookService $webhookService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?InvoiceOutput
    {
        unset($data, $context);

        $operationName = $operation->getName() ?? '';

        $invoice = match ($operationName) {
            'generate_order_invoice' => $this->invoices->generateForOrder((string) ($uriVariables['boutiqueId'] ?? ''), (string) ($uriVariables['orderId'] ?? '')),
            'generate_subscription_invoice' => $this->invoices->generateForSubscription((string) ($uriVariables['subscriptionId'] ?? '')),
            'mark_invoice_paid' => $this->invoices->markPaid((string) ($uriVariables['id'] ?? '')),
            default => null,
        };

        if (null === $invoice) {
            return null;
        }

        $boutiqueId = (string) $invoice->getBoutique()->getId();
        $payload = [
            'id' => (string) $invoice->getId(),
            'invoice_number' => $invoice->getInvoiceNumber(),
            'status' => $invoice->getStatus()->value,
            'type' => $invoice->getType()->value,
            'total' => $invoice->getTotal(),
            'currency' => $invoice->getCurrency(),
            'customer_name' => $invoice->getCustomerName(),
            'customer_email' => $invoice->getCustomerEmail(),
            'order_id' => $invoice->getOrder() ? (string) $invoice->getOrder()->getId() : null,
            'subscription_id' => $invoice->getSubscription() ? (string) $invoice->getSubscription()->getId() : null,
        ];

        match ($operationName) {
            'generate_order_invoice', 'generate_subscription_invoice' => $this->webhookService->dispatchEvent('invoice.created', $payload, $boutiqueId),
            'mark_invoice_paid' => $this->webhookService->dispatchEvent('invoice.paid', $payload, $boutiqueId),
            default => null,
        };

        return $this->provider->provide(new \ApiPlatform\Metadata\Get(), ['id' => (string) $invoice->getId()]);
    }
}
