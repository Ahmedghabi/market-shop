<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetInput
{
    #[Assert\NotBlank] public string $token;
    #[Assert\NotBlank] #[Assert\Length(min: 8)] public string $password;
}
