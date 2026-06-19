<?php

namespace App\Dto\SubscriptionModule;

final class SubscriptionModuleOutput
{
    public ?string $id = null;
    public ?string $planId = null;
    public ?string $planName = null;
    public ?string $moduleId = null;
    public ?string $moduleCode = null;
    public ?string $moduleName = null;
    public bool $isAllowed = true;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
