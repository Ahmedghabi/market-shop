<?php

namespace App\Dto\Stock;

final class StockMovementOutput
{
    public string $id;
    public string $boutiqueId;
    public string $productId;
    public string $productName;
    public string $type;
    public int $quantity;
    public int $previousQuantity;
    public int $newQuantity;
    public ?string $reference;
    public ?string $reason;
    public \DateTimeImmutable $createdAt;
}
