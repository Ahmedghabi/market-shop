<?php

namespace App\Dto\SubscriptionModule;

use Symfony\Component\Validator\Constraints as Assert;

final class SubscriptionModuleInput
{
    public function __construct(
        #[Assert\NotBlank]
        public string $planId,
        #[Assert\NotBlank]
        public string $moduleId,
        public bool $isAllowed = true,
    ) {
    }
}
