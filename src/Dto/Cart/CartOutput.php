<?php

namespace App\Dto\Cart;

final class CartOutput
{
    public string $id;
    public string $boutiqueId;
    public string $status;
    public string $currency;
    public int $itemsCount;
    public int $totalCents;
    /** @var list<CartItemOutput> */
    public array $items = [];
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
    public \DateTimeImmutable $expiresAt;
}
