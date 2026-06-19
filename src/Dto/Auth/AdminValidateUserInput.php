<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class AdminValidateUserInput
{
    #[Assert\NotBlank] #[Assert\Choice(['approve', 'reject', 'suspend', 'activate'])] public string $action;
}
