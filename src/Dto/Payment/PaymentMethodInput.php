<?php

namespace App\Dto\Payment;

final class PaymentMethodInput
{
    public string $name;
    public string $code;
    public ?string $description = null;
    public ?string $logo = null;
    public string $type = 'EXTERNAL_GATEWAY';
    public bool $isActive = true;
    public bool $isVisible = true;
}
