<?php

namespace App\Dto\ShopSocialProvider;

use Symfony\Component\Validator\Constraints as Assert;

final class ShopSocialProviderInput
{
    #[Assert\NotBlank] public string $socialProviderId;
    public bool $isActive = false;
}
