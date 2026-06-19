<?php

namespace App\State\Refund;

use App\Dto\Refund\RefundOutput;
use App\Entity\Refund;
use App\Repository\RefundRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class RefundProvider implements ProviderInterface
{
    public function __construct(
        private RefundRepository $refunds,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?RefundOutput
    {
        $refund = $this->refunds->find($uriVariables['id'] ?? null);
        if (!$refund instanceof Refund) {
            return null;
        }

        return $this->toOutput($refund);
    }

    /** @return list<RefundOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? null;
        $refunds = $boutiqueId ? $this->refunds->findByBoutique($boutiqueId) : [];

        return array_map($this->toOutput(...), $refunds);
    }

    private function toOutput(Refund $refund): RefundOutput
    {
        $items = [];
        foreach ($refund->getItems() as $item) {
            $items[] = [
                'id' => (string) $item->getId(),
                'productName' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'unitPriceCents' => $item->getUnitPriceCents(),
                'totalCents' => $item->getTotalCents(),
            ];
        }

        return new RefundOutput(
            id: (string) $refund->getId(),
            refundNumber: $refund->getRefundNumber(),
            orderId: (string) $refund->getOrder()->getId(),
            orderNumber: $refund->getOrder()->getId(),
            type: $refund->getType()->value,
            status: $refund->getStatus()->value,
            currency: $refund->getCurrency(),
            subtotalCents: $refund->getSubtotalCents(),
            taxCents: $refund->getTaxCents(),
            totalCents: $refund->getTotalCents(),
            reason: $refund->getReason(),
            processedBy: $refund->getProcessedBy(),
            processedAt: $refund->getProcessedAt()?->format('c'),
            creditNoteId: $refund->getCreditNote() ? (string) $refund->getCreditNote()->getId() : null,
            createdAt: $refund->getCreatedAt()->format('c'),
            updatedAt: $refund->getUpdatedAt()?->format('c'),
            items: $items,
        );
    }
}
