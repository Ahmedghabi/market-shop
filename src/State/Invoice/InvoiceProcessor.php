<?php

namespace App\State\Invoice;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Invoice\InvoiceOutput;
use App\Service\Billing\InvoiceService;

/** @implements ProcessorInterface<InvoiceOutput|null> */
final readonly class InvoiceProcessor implements ProcessorInterface
{
    public function __construct(
        private InvoiceService $invoices,
        private InvoiceProvider $provider,
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

        return $this->provider->provide(new \ApiPlatform\Metadata\Get(), ['id' => (string) $invoice->getId()]);
    }
}
