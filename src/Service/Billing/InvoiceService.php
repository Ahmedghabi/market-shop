<?php

namespace App\Service\Billing;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Order;
use App\Entity\Subscription;
use App\Enum\InvoiceStatus;
use App\Enum\InvoiceType;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class InvoiceService
{
    public function __construct(
        private InvoiceRepository $invoices,
        private OrderRepository $orders,
        private SubscriptionRepository $subscriptions,
        private EntityManagerInterface $em,
        private InvoiceCacheService $cache,
    ) {
    }

    public function generateForOrder(string $boutiqueId, string $orderId): Invoice
    {
        $order = $this->orders->find($orderId);
        if (!$order instanceof Order || (string) $order->getBoutique()->getId() !== $boutiqueId) {
            throw new NotFoundHttpException('Order not found');
        }

        $existing = $this->invoices->findOneByOrder($order);
        if ($existing instanceof Invoice) {
            return $existing;
        }

        $invoice = new Invoice(
            invoiceNumber: $this->nextInvoiceNumber($order->getBoutique()->getSlug()),
            boutique: $order->getBoutique(),
            customer: $order->getCustomer(),
            order: $order,
            subscription: null,
            type: InvoiceType::Order,
            status: InvoiceStatus::Pending,
            currency: $order->getCurrency(),
        );
        $invoice->setTotals($order->getSubtotalCents(), $order->getDiscountCents(), 0, 0, $order->getTotalCents());
        $invoice->setBoutiqueSnapshot($order->getBoutique()->getName(), $order->getBoutique()->getContactEmail(), $order->getBoutique()->getContactPhone(), $order->getBoutique()->getAddress());
        $invoice->setCustomerSnapshot(
            $order->getCustomerName(),
            $order->getCustomerEmail(),
            $order->getCustomerPhone(),
            $order->getShippingAddress(),
            $order->getShippingCity(),
            $order->getShippingPostalCode(),
            $order->getShippingCountry(),
            $order->getShippingCountryId(),
            $order->getShippingGovernorate(),
            $order->getShippingGovernorateId(),
            $order->getShippingLocality(),
            $order->getShippingLocalityId(),
        );
        if ('paid' === $order->getPaymentStatus()->value) {
            $invoice->markPaid();
        }

        foreach ($order->getItems() as $orderItem) {
            $item = new InvoiceItem(
                invoice: $invoice,
                product: null,
                description: $orderItem->getProductName(),
                quantity: $orderItem->getQuantity(),
                unitPrice: $orderItem->getUnitPriceCents(),
                discount: 0,
                tax: 0,
                total: $orderItem->getQuantity() * $orderItem->getUnitPriceCents(),
            );
            $invoice->addItem($item);
            $this->em->persist($item);
        }

        $this->em->persist($invoice);
        $this->em->flush();
        $this->cache->invalidateShopInvoices((string) $order->getBoutique()->getId());

        return $invoice;
    }

    public function generateForSubscription(string $subscriptionId): Invoice
    {
        $subscription = $this->subscriptions->find($subscriptionId);
        if (!$subscription instanceof Subscription) {
            throw new NotFoundHttpException('Subscription not found');
        }

        $existing = $this->invoices->findOneBySubscription($subscription);
        if ($existing instanceof Invoice) {
            return $existing;
        }

        $plan = $subscription->getSubscriptionPlan();
        $amount = $plan?->getPriceTnd() ?? 0;
        $invoice = new Invoice(
            invoiceNumber: $this->nextInvoiceNumber($subscription->getBoutique()->getSlug()),
            boutique: $subscription->getBoutique(),
            customer: null,
            order: null,
            subscription: $subscription,
            type: InvoiceType::Subscription,
            status: InvoiceStatus::Pending,
            currency: 'TND',
        );
        $invoice->setTotals($amount, 0, 0, 0, $amount);
        $invoice->setBoutiqueSnapshot($subscription->getBoutique()->getName(), $subscription->getBoutique()->getContactEmail(), $subscription->getBoutique()->getContactPhone(), $subscription->getBoutique()->getAddress());
        $invoice->setDueDate(($subscription->getStartDate() ?? new \DateTimeImmutable())->modify('+7 days'));

        $item = new InvoiceItem(
            invoice: $invoice,
            product: null,
            description: sprintf('Subscription: %s', $plan?->getName() ?? $subscription->getPlan()->value),
            quantity: 1,
            unitPrice: $amount,
            discount: 0,
            tax: 0,
            total: $amount,
        );
        $invoice->addItem($item);

        $this->em->persist($invoice);
        $this->em->persist($item);
        $this->em->flush();
        $this->cache->invalidateShopInvoices((string) $subscription->getBoutique()->getId());

        return $invoice;
    }

    public function markPaid(string $invoiceId): Invoice
    {
        $invoice = $this->invoices->find($invoiceId);
        if (!$invoice instanceof Invoice) {
            throw new NotFoundHttpException('Invoice not found');
        }

        $invoice->markPaid();
        $this->em->flush();
        $this->cache->invalidateInvoice($invoiceId);
        $this->cache->invalidateShopInvoices((string) $invoice->getBoutique()->getId());

        return $invoice;
    }

    private function nextInvoiceNumber(string $boutiqueSlug): string
    {
        $year = (int) (new \DateTimeImmutable())->format('Y');
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $boutiqueSlug) ?: 'INV', 0, 6));
        $sequence = $this->invoices->nextSequence($prefix, $year);

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    }
}
