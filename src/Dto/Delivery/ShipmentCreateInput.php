<?php

namespace App\Dto\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

final class ShipmentCreateInput
{
    #[Assert\NotBlank]
    public string $orderId = '';

    public ?string $accountId = null;
}
