<?php

namespace App\Dto\SubscriptionRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class SubscriptionRequestInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    public string $subscriptionPlanId;
}
