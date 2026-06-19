<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class LoginInput
{
    #[Assert\NotBlank] #[Assert\Email] public string $email;
    #[Assert\NotBlank] public string $password;
}
