<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class SocialLoginInput
{
    #[Assert\NotBlank] public string $provider;
    #[Assert\NotBlank] public string $providerToken;
}
