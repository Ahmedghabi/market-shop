<?php

namespace App\Dto\Customer;

use Symfony\Component\Validator\Constraints as Assert;

final class CustomerInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstName;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $lastName;

    public ?string $phone = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $address = null;

    public ?string $city = null;

    public ?string $postalCode = null;

    public ?string $country = 'France';
}
