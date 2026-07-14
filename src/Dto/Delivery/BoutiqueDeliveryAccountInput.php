<?php

namespace App\Dto\Delivery;

final class BoutiqueDeliveryAccountInput
{
    public ?string $deliveryCompanyId = null;

    public ?string $login = null;

    public ?string $password = null;

    public ?string $apiKey = null;

    public ?string $token = null;

    public ?string $secret = null;

    public ?string $customBaseUrl = null;

    public ?bool $isActive = null;

    public ?bool $isDefault = null;
}
