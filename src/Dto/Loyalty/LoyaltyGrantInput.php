<?php

namespace App\Dto\Loyalty;

use Symfony\Component\Validator\Constraints as Assert;

final class LoyaltyGrantInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    public string $customerId = '';

    #[Assert\NotEqualTo(0)]
    public int $points = 0;

    #[Assert\NotBlank]
    public string $reason = '';
}
