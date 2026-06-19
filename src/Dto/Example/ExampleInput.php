<?php

namespace App\Dto\Example;

use Symfony\Component\Validator\Constraints as Assert;

final class ExampleInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    public ?string $name = null;
}
