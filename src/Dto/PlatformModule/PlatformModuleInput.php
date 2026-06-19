<?php

namespace App\Dto\PlatformModule;

use Symfony\Component\Validator\Constraints as Assert;

final class PlatformModuleInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $moduleId,
        public bool $isEnabled = true,
        public ?string $reasonDisabled = null,
    ) {
    }
}
