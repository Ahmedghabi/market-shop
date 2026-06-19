<?php

namespace App\Dto\PlatformModule;

final class PlatformModuleOutput
{
    public ?string $id = null;
    public ?string $moduleId = null;
    public ?string $moduleCode = null;
    public ?string $moduleName = null;
    public bool $isEnabled = true;
    public ?string $reasonDisabled = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
