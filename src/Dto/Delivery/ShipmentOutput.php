<?php

namespace App\Dto\Delivery;

final class ShipmentOutput
{
    public string $id;
    public string $boutiqueId;
    public string $orderId;
    public string $deliveryCompanyId;
    public string $deliveryCompanyName;
    public ?string $credentialId;
    public string $status;
    public ?string $trackingNumber;
    public ?string $labelUrl;
    public ?int $costCents;
    public ?string $errorMessage;
    public string $createdAt;
    public ?string $sentAt;
    public ?string $updatedAt;
}
