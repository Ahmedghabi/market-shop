<?php

namespace App\State\Refund;

use App\Dto\Refund\RefundInput;
use App\Enum\RefundType;
use App\Service\Billing\RefundService;
use App\Service\Webhook\WebhookService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final class RefundProcessor implements ProcessorInterface
{
    public function __construct(
        private RefundService $refundService,
        private WebhookService $webhookService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $request = $context['request'] ?? null;
        $routeName = $request?->attributes->get('_route') ?? '';

        if ('refund_approve' === $operation->getName()) {
            $refund = $this->refundService->approveRefund(
                $uriVariables['id'] ?? '',
                $request?->getUser()?->getUserIdentifier(),
            );

            $this->dispatchRefundEvent('refund.created', $refund);

            return $refund;
        }

        if ('refund_process' === $operation->getName()) {
            $refund = $this->refundService->processRefund(
                $uriVariables['id'] ?? '',
                $request?->getUser()?->getUserIdentifier(),
            );

            $this->dispatchRefundEvent('refund.processed', $refund);

            return $refund;
        }

        if ('refund_reject' === $operation->getName()) {
            return $this->refundService->rejectRefund(
                $uriVariables['id'] ?? '',
                $request?->getUser()?->getUserIdentifier(),
            );
        }

        $boutiqueId = $uriVariables['boutiqueId'] ?? '';

        if ($data instanceof RefundInput) {
            $refund = $this->refundService->createRefund(
                boutiqueId: $boutiqueId,
                orderId: $data->orderId ?? '',
                type: RefundType::from($data->type ?? 'FULL'),
                reason: $data->reason,
                items: $data->items,
            );

            $this->dispatchRefundEvent('refund.created', $refund);

            return $refund;
        }

        return null;
    }

    private function dispatchRefundEvent(string $eventName, \App\Entity\Refund $refund): void
    {
        $boutiqueId = (string) $refund->getBoutique()->getId();
        $this->webhookService->dispatchEvent($eventName, [
            'id' => (string) $refund->getId(),
            'refund_number' => $refund->getRefundNumber(),
            'order_id' => (string) $refund->getOrder()->getId(),
            'status' => $refund->getStatus()->value,
            'type' => $refund->getType()->value,
            'total_cents' => $refund->getTotalCents(),
            'currency' => $refund->getCurrency(),
            'reason' => $refund->getReason(),
            'customer_name' => $refund->getCustomer()?->getFirstName().' '.$refund->getCustomer()?->getLastName(),
        ], $boutiqueId);
    }
}
