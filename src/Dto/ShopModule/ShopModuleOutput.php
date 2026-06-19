<?php

namespace App\Dto\ShopModule;

final class ShopModuleOutput
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $moduleId = null;
    public ?string $moduleCode = null;
    public ?string $moduleName = null;
    public ?string $moduleCategory = null;
    public bool $isEnabled = true;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;
}
