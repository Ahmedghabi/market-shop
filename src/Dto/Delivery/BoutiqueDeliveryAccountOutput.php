<?php

namespace App\Dto\Delivery;

final class BoutiqueDeliveryAccountOutput
{
    public string $id;
    public string $deliveryCompanyId;
    public string $deliveryCompanyName;
    public bool $isVerified;
    public ?string $verifiedAt;
    public ?string $lastError;
    public bool $isActive;
    public string $createdAt;
}
