<?php

namespace App\Dto\Order;

use Symfony\Component\Validator\Constraints as Assert;

final class OrderInput
{
    #[Assert\NotBlank]
    public string $customerId;

    #[Assert\Choice(['online', 'pos'])]
    public string $channel = 'online';

    #[Assert\Choice(['draft', 'pending', 'paid', 'completed', 'shipped', 'delivered', 'cancelled', 'refunded'])]
    public string $status = 'pending';

    #[Assert\PositiveOrZero]
    public int $discountCents = 0;

    public string $currency = 'TND';

    /** @var list<OrderItemInput> */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $items = [];
}
