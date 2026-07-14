<?php

namespace App\Dto\QuotaDefinition;

final class QuotaDefinitionOutput
{
    public ?string $id = null;
    public string $code;
    public string $name;
    public ?string $description = null;
    public ?string $unit = null;
    public ?string $category = null;
    public ?string $icon = null;
    public int $priceTnd = 0;
    public bool $isActive = true;
    public ?string $createdAt = null;
}
