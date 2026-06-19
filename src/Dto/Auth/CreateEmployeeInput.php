<?php

namespace App\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateEmployeeInput
{
    #[Assert\NotBlank] #[Assert\Length(max: 120)] public string $firstname;
    #[Assert\NotBlank] #[Assert\Length(max: 120)] public string $lastname;
    #[Assert\NotBlank] #[Assert\Email] public string $email;
    #[Assert\NotBlank] #[Assert\Length(max: 64)] public string $phone;
    #[Assert\NotBlank] #[Assert\Length(min: 8)] public string $password;
    #[Assert\NotBlank] public string $boutiqueId;
    #[Assert\Choice(['ROLE_CAISSIER'])] public string $role = 'ROLE_CAISSIER';
}
