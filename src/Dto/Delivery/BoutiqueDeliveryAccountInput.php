<?php

namespace App\Dto\Delivery;

use Symfony\Component\Validator\Constraints as Assert;

final class BoutiqueDeliveryAccountInput
{
    #[Assert\NotBlank]
    public string $deliveryCompanyId;

    #[Assert\NotBlank]
    public string $login;

    #[Assert\NotBlank]
    public string $password;
}
