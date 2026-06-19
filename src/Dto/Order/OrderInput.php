<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderInput
{
    #[Assert\NotBlank]
    public string $customerId;

    #[Assert\Choice(['online', 'pos'])]
    public string $channel = 'online';

    #[Assert\Choice(['pending', 'confirmed', 'preparing', 'ready', 'shipped', 'delivered', 'cancelled'])]
    public string $status = 'pending';

    #[Assert\PositiveOrZero]
    public int $discountCents = 0;

    public string $currency = 'EUR';

    /** @var list<OrderItemInput> */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
