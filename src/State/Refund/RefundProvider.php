<?php

namespace App\State\Refund;

use App\Dto\Refund\RefundOutput;
use App\Entity\Refund;
use App\Repository\RefundRepository;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Entity\Boutique;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class RefundProvider implements ProviderInterface
{
    public function __construct(
        private RefundRepository $refunds,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?RefundOutput
    {
        $refund = $this->refunds->find($uriVariables['id'] ?? null);
        if (!$refund instanceof Refund || !$this->canAccess($refund, $context)) {
            return null;
        }

        return $this->toOutput($refund);
    }

    /** @return list<RefundOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $boutique = $this->resolveBoutique($context, $uriVariables);
        if (!$boutique instanceof Boutique) {
            return [];
        }

        $refunds = $this->refunds->findByBoutique((string) $boutique->getId());

        return array_map($this->toOutput(...), $refunds);
    }

    private function canAccess(Refund $refund, array $context): bool
    {
        if ($this->context->isSuperAdmin()) {
            return true;
        }

        $boutique = $this->resolveBoutique($context);

        return $boutique instanceof Boutique
            && (string) $refund->getBoutique()->getId() === (string) $boutique->getId();
    }

    /** @param array<string, mixed> $context @param array<string, mixed> $uriVariables */
    private function resolveBoutique(array $context, array $uriVariables = []): ?Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof \Symfony\Component\HttpFoundation\Request
            ? $request->attributes->get('_boutique')
            : null;
        if ($boutique instanceof Boutique) {
            return $boutique;
        }

        $id = $uriVariables['boutiqueId'] ?? $this->context->getBoutiqueId();

        return null !== $id ? $this->boutiques->find((string) $id) : null;
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
