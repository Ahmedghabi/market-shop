<?php

namespace App\Service\Billing;

use App\Entity\Refund;
use App\Entity\RefundItem;
use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Order;
use App\Enum\InvoiceStatus;
use App\Enum\InvoiceType;
use App\Enum\RefundStatus;
use App\Enum\RefundType;
use App\Repository\InvoiceRepository;
use App\Repository\OrderRepository;
use App\Repository\RefundRepository;
use App\Service\Loyalty\LoyaltyEngine;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class RefundService
{
    public function __construct(
        private RefundRepository $refunds,
        private InvoiceRepository $invoices,
        private OrderRepository $orders,
        private EntityManagerInterface $em,
        private RefundCacheService $cache,
        private LoyaltyEngine $loyaltyEngine,
        private BoutiqueContext $boutiqueContext,
    ) {
    }

    public function createRefund(
        string $boutiqueId,
        string $orderId,
        RefundType $type,
        ?string $reason = null,
        ?array $items = null,
    ): Refund {
        $order = $this->orders->find($orderId);
        if (!$order instanceof Order || (string) $order->getBoutique()->getId() !== $boutiqueId) {
            throw new NotFoundHttpException('Order not found');
        }

        $existingFull = $this->refunds->findOneByOrderAndType($orderId, RefundType::Full->value);
        if ($existingFull instanceof Refund && RefundType::Full === $type) {
            throw new \DomainException('A full refund already exists for this order.');
        }

        $refundNumber = $this->nextRefundNumber($boutiqueId, $order->getCreatedAt()->format('Y'));
        $subtotalCents = 0;
        $taxCents = 0;

        $refund = new Refund(
            refundNumber: $refundNumber,
            boutique: $order->getBoutique(),
            order: $order,
            customer: $order->getCustomer(),
            type: $type,
            status: RefundStatus::Pending,
            currency: $order->getCurrency(),
        );
        $refund->setReason($reason);
        $this->em->persist($refund);

        if (RefundType::Full === $type) {
            $subtotalCents = $order->getSubtotalCents();
            $refund->setTotals($subtotalCents, $taxCents, $subtotalCents);
        } elseif (RefundType::ItemLevel === $type && is_array($items)) {
            foreach ($items as $itemData) {
                $orderItem = $order->getItems()->first();
                $quantity = (int) ($itemData['quantity'] ?? 1);
                $unitPrice = (int) ($itemData['unitPriceCents'] ?? 0);
                $itemTotal = $quantity * $unitPrice;

                $refundItem = new RefundItem(
                    refund: $refund,
                    orderItem: null,
                    productName: (string) ($itemData['productName'] ?? 'Item'),
                    quantity: $quantity,
                    unitPriceCents: $unitPrice,
                    totalCents: $itemTotal,
                );
                $this->em->persist($refundItem);
                $refund->addItem($refundItem);
                $subtotalCents += $itemTotal;
            }
            $refund->setTotals($subtotalCents, $taxCents, $subtotalCents);
        }

        $this->em->flush();
        $this->cache->invalidate($refundNumber);
        $this->cache->invalidateShop((string) $order->getBoutique()->getId());

        return $refund;
    }

    public function approveRefund(string $refundId, ?string $processedBy = null): Refund
    {
        $refund = $this->refunds->find($refundId);
        if (!$refund instanceof Refund) {
            throw new NotFoundHttpException('Refund not found');
        }
        $this->assertAccessible($refund);

        if (RefundStatus::Pending !== $refund->getStatus()) {
            throw new \DomainException('Only pending refunds can be approved.');
        }

        $refund->approve($processedBy);

        $creditNote = $this->createCreditNote($refund);
        $refund->setCreditNote($creditNote);

        $order = $refund->getOrder();
        $order->setPaymentStatus(\App\Enum\PaymentStatus::Refunded);

        $this->em->flush();
        $this->cache->invalidate($refundId);
        $this->cache->invalidateShop((string) $refund->getBoutique()->getId());

        // Loyalty restitution/revocation is proportional to the refunded amount —
        // full refund reverses 100%, partial refund reverses the matching ratio.
        $orderTotalCents = $order->getTotalCents();
        $refundRatio = $orderTotalCents > 0 ? min(1.0, $refund->getTotalCents() / $orderTotalCents) : 1.0;
        $this->loyaltyEngine->reverseForOrder($order, $refundRatio);

        return $refund;
    }

    public function processRefund(string $refundId, ?string $processedBy = null): Refund
    {
        $refund = $this->refunds->find($refundId);
        if (!$refund instanceof Refund) {
            throw new NotFoundHttpException('Refund not found');
        }
        $this->assertAccessible($refund);

        if (RefundStatus::Approved !== $refund->getStatus()) {
            throw new \DomainException('Only approved refunds can be processed.');
        }

        $refund->markProcessed($processedBy);
        $this->em->flush();
        $this->cache->invalidate($refundId);
        $this->cache->invalidateShop((string) $refund->getBoutique()->getId());

        return $refund;
    }

    public function rejectRefund(string $refundId, ?string $processedBy = null): Refund
    {
        $refund = $this->refunds->find($refundId);
        if (!$refund instanceof Refund) {
            throw new NotFoundHttpException('Refund not found');
        }
        $this->assertAccessible($refund);

        if (RefundStatus::Pending !== $refund->getStatus()) {
            throw new \DomainException('Only pending refunds can be rejected.');
        }

        $refund->reject($processedBy);
        $this->em->flush();
        $this->cache->invalidate($refundId);
        $this->cache->invalidateShop((string) $refund->getBoutique()->getId());

        return $refund;
    }

    public function getRefundsForBoutique(string $boutiqueId): array
    {
        return $this->refunds->findByBoutique($boutiqueId);
    }

    public function getRefundById(string $refundId): ?Refund
    {
        $refund = $this->refunds->find($refundId);

        return $refund instanceof Refund && $this->boutiqueContext->canAccessBoutique($refund->getBoutique()) ? $refund : null;
    }

    private function assertAccessible(Refund $refund): void
    {
        if (!$this->boutiqueContext->canAccessBoutique($refund->getBoutique())) {
            throw new NotFoundHttpException('Refund not found');
        }
    }

    private function createCreditNote(Refund $refund): Invoice
    {
        $invoiceNumber = $this->nextCreditNoteNumber(
            $refund->getBoutique()->getSlug(),
            $refund->getCreatedAt()->format('Y'),
        );

        $invoice = new Invoice(
            invoiceNumber: $invoiceNumber,
            boutique: $refund->getBoutique(),
            customer: $refund->getCustomer(),
            order: $refund->getOrder(),
            subscription: null,
            type: InvoiceType::CreditNote,
            status: InvoiceStatus::Paid,
            currency: $refund->getCurrency(),
        );
        $invoice->setTotals(
            $refund->getSubtotalCents(),
            0,
            $refund->getTaxCents(),
            0,
            $refund->getTotalCents(),
        );
        $invoice->setBoutiqueSnapshot(
            $refund->getBoutique()->getName(),
            $refund->getBoutique()->getContactEmail(),
            $refund->getBoutique()->getContactPhone(),
            $refund->getBoutique()->getAddress(),
        );
        $invoice->setCustomerSnapshot(
            $refund->getOrder()->getCustomerName(),
            $refund->getOrder()->getCustomerEmail(),
            $refund->getOrder()->getCustomerPhone(),
            $refund->getOrder()->getShippingAddress(),
            $refund->getOrder()->getShippingCity(),
            $refund->getOrder()->getShippingPostalCode(),
            $refund->getOrder()->getShippingCountry(),
            $refund->getOrder()->getShippingCountryId(),
            $refund->getOrder()->getShippingGovernorate(),
            $refund->getOrder()->getShippingGovernorateId(),
            $refund->getOrder()->getShippingLocality(),
            $refund->getOrder()->getShippingLocalityId(),
        );
        $invoice->markPaid();

        foreach ($refund->getItems() as $refundItem) {
            $item = new InvoiceItem(
                invoice: $invoice,
                product: null,
                description: 'Remboursement: '.$refundItem->getProductName(),
                quantity: $refundItem->getQuantity(),
                unitPrice: $refundItem->getUnitPriceCents(),
                discount: 0,
                tax: 0,
                total: $refundItem->getTotalCents(),
            );
            $invoice->addItem($item);
            $this->em->persist($item);
        }

        $this->em->persist($invoice);

        return $invoice;
    }

    private function nextRefundNumber(string $boutiqueSlug, string $year): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $boutiqueSlug) ?: 'RFD', 0, 6));
        $sequence = $this->refunds->nextSequence($boutiqueSlug, (int) $year);

        return sprintf('%s-R-%s-%06d', $prefix, $year, $sequence);
    }

    private function nextCreditNoteNumber(string $boutiqueSlug, string $year): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $boutiqueSlug) ?: 'CN', 0, 6));
        $sequence = $this->invoices->nextSequence($prefix, (int) $year);

        return sprintf('%s-CN-%s-%06d', $prefix, $year, $sequence);
    }
}
