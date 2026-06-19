<?php

namespace App\Dto\Refund;

final class RefundOutput
{
    public function __construct(
        public string $id,
        public string $refundNumber,
        public string $orderId,
        public string $orderNumber,
        public string $type,
        public string $status,
        public string $currency,
        public int $subtotalCents,
        public int $taxCents,
        public int $totalCents,
        public ?string $reason,
        public ?string $processedBy,
        public ?string $processedAt,
        public ?string $creditNoteId,
        public string $createdAt,
        public ?string $updatedAt,
        public array $items = [],
    ) {
    }
}
