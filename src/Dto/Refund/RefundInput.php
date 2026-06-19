<?php

namespace App\Dto\Refund;

final class RefundInput
{
    public ?string $orderId = null;
    public ?string $type = null;
    public ?string $reason = null;
    /** @var array<array{productName?: string, quantity?: int, unitPriceCents?: int}>|null */
    public ?array $items = null;
}
