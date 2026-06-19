<?php

namespace App\Dto\SubscriptionRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class SubscriptionRequestInput
{
    #[Assert\NotBlank]
    public string $subscriptionPlanId;
}
