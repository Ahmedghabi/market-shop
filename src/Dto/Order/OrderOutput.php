<?php

namespace App\Dto\Order;

final class OrderOutput
{
    public string $id;
    public string $boutiqueId;
    public string $customerId;
    public string $customerName;
    public string $customerEmail;
    public string $channel;
    public string $status;
    public int $subtotalCents;
    public int $discountCents;
    public int $totalCents;
    public string $currency;
    public array $items;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
}
