<?php

namespace App\Dto\Subscription;

use Symfony\Component\Validator\Constraints as Assert;

final class SubscriptionInput
{
    #[Assert\NotBlank]
    #[Assert\Choice(['3months', '6months', '1year'])]
    public string $plan;
}
