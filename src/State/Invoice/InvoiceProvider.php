<?php

namespace App\State\Invoice;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Invoice\InvoiceOutput;
use App\Entity\Invoice;
use App\Repository\BoutiqueRepository;
use App\Repository\InvoiceRepository;
use App\Service\Billing\InvoiceCacheService;

/** @implements ProviderInterface<InvoiceOutput> */
final readonly class InvoiceProvider implements ProviderInterface
{
    public function __construct(
        private InvoiceRepository $invoices,
        private BoutiqueRepository $boutiques,
        private InvoiceCacheService $cache,
    ) {
    }

    /** @return list<InvoiceOutput>|InvoiceOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|InvoiceOutput|null
    {
        unset($context);

        $invoiceId = $uriVariables['id'] ?? null;
        if ($operation instanceof Get && null !== $invoiceId) {
            return $this->cache->getInvoice((string) $invoiceId, function () use ($invoiceId): ?InvoiceOutput {
                $invoice = $this->invoices->find((string) $invoiceId);

                return $invoice instanceof Invoice ? $this->toOutput($invoice) : null;
            });
        }

        $boutiqueId = $uriVariables['boutiqueId'] ?? null;
        if ($boutiqueId) {
            $boutique = $this->boutiques->findBySlugOrId((string) $boutiqueId);
            if (!$boutique) {
                return [];
            }

            return $this->cache->getShopInvoices((string) $boutique->getId(), fn (): array => array_map([$this, 'toOutput'], $this->invoices->findByBoutique($boutique)));
        }

        return array_map([$this, 'toOutput'], $this->invoices->findBy([], ['createdAt' => 'DESC']));
    }

    private function toOutput(Invoice $invoice): InvoiceOutput
    {
        $output = new InvoiceOutput();
        $output->id = (string) $invoice->getId();
        $output->invoiceNumber = $invoice->getInvoiceNumber();
        $output->boutiqueId = (string) $invoice->getBoutique()->getId();
        $output->customerId = $invoice->getCustomer() ? (string) $invoice->getCustomer()->getId() : null;
        $output->orderId = $invoice->getOrder() ? (string) $invoice->getOrder()->getId() : null;
        $output->subscriptionId = $invoice->getSubscription() ? (string) $invoice->getSubscription()->getId() : null;
        $output->type = $invoice->getType()->value;
        $output->status = $invoice->getStatus()->value;
        $output->currency = $invoice->getCurrency();
        $output->subtotal = $invoice->getSubtotal();
        $output->discountTotal = $invoice->getDiscountTotal();
        $output->taxTotal = $invoice->getTaxTotal();
        $output->shippingTotal = $invoice->getShippingTotal();
        $output->total = $invoice->getTotal();
        $output->issuedAt = $invoice->getIssuedAt()->format('c');
        $output->dueDate = $invoice->getDueDate()?->format('c');
        $output->paidAt = $invoice->getPaidAt()?->format('c');
        $output->pdfPath = $invoice->getPdfPath();
        $output->boutiqueName = $invoice->getBoutiqueName();
        $output->boutiqueEmail = $invoice->getBoutiqueEmail();
        $output->boutiquePhone = $invoice->getBoutiquePhone();
        $output->boutiqueAddress = $invoice->getBoutiqueAddress();
        $output->customerName = $invoice->getCustomerName();
        $output->customerEmail = $invoice->getCustomerEmail();
        $output->customerPhone = $invoice->getCustomerPhone();
        $output->customerAddress = $invoice->getCustomerAddress();
        $output->customerCity = $invoice->getCustomerCity();
        $output->customerPostalCode = $invoice->getCustomerPostalCode();
        $output->customerCountry = $invoice->getCustomerCountry();
        $output->items = array_map(fn ($item) => [
            'productId' => $item->getProduct() ? (string) $item->getProduct()->getId() : null,
            'description' => $item->getDescription(),
            'quantity' => $item->getQuantity(),
            'unitPrice' => $item->getUnitPrice(),
            'discount' => $item->getDiscount(),
            'tax' => $item->getTax(),
            'total' => $item->getTotal(),
        ], $invoice->getItems()->toArray());
        $output->createdAt = $invoice->getCreatedAt()->format('c');
        $output->updatedAt = $invoice->getUpdatedAt()?->format('c');

        return $output;
    }
}
