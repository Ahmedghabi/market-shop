<?php

namespace App\Dto\SocialProvider;

use Symfony\Component\Validator\Constraints as Assert;

final class SocialProviderInput
{
    #[Assert\NotBlank] #[Assert\Length(max: 32)] public string $code;
    #[Assert\NotBlank] #[Assert\Length(max: 100)] public string $name;
    public bool $isActive = false;
}
