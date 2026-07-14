<?php

namespace App\Dto\Loyalty;

final class LoyaltyTransactionOutput
{
    public string $id;
    public string $type;
    public int $points;
    public ?int $discountCents;
    public ?string $orderId;
    public ?string $reason;
    public ?string $expiresAt;
    public \DateTimeImmutable $createdAt;
}
