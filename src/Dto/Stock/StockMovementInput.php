<?php

namespace App\Dto\Stock;

use Symfony\Component\Validator\Constraints as Assert;

final class StockMovementInput
{
    #[Assert\NotBlank]
    public string $productId;

    #[Assert\Choice(['in', 'out', 'adjustment', 'reservation', 'release'])]
    public string $type = 'adjustment';

    #[Assert\NotEqualTo(0)]
    public int $quantity = 0;

    public ?string $reference = null;

    public ?string $reason = null;
}
