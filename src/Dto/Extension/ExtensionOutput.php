<?php

namespace App\Dto\Extension;

final class ExtensionOutput
{
    public ?string $id = null;
    public string $code;
    public string $name;
    public ?string $description = null;
    public string $type;
    public ?string $targetCode = null;
    public ?int $value = null;
    public int $priceTnd = 0;
    public ?int $durationMonths = null;
    public bool $requiresValidation = true;
    public bool $isActive = true;
    public ?string $icon = null;
    public bool $isFree = false;
    public bool $isPermanent = true;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    /**
     * Only populated on the boutique-facing "available" listing.
     */
    public bool $alreadyActive = false;
    public ?string $pendingRequestId = null;
    public ?string $pendingRequestStatus = null;
}
