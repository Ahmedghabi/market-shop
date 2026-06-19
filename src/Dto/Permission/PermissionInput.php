<?php

namespace App\Dto\Permission;

use Symfony\Component\Validator\Constraints as Assert;

final class PermissionInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $code,
        #[Assert\NotBlank]
        #[Assert\Length(max: 160)]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Length(max: 64)]
        public string $module,
        public ?string $description = null,
    ) {
    }
}
