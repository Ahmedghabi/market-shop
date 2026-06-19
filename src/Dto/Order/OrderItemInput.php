<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderItemInput
{
    #[Assert\NotBlank]
    public string $productId;

    #[Assert\Positive]
    public int $quantity = 1;

    #[Assert\PositiveOrZero]
    public int $unitPriceCents = 0;

    #[Assert\PositiveOrZero]
    public int $discountCents = 0;
}
