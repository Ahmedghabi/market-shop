<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

final class UserInput
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $displayName;

    /** @var list<string> */
    #[Assert\All(new Assert\Choice(['ROLE_BOUTIQUE_ADMIN', 'ROLE_CAISSIER']))]
    public array $roles = ['ROLE_CAISSIER'];

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public ?string $plainPassword = null;
}
