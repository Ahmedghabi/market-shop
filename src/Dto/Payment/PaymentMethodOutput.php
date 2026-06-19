<?php

namespace App\Dto\Payment;

final class PaymentMethodOutput
{
    public string $id;
    public string $name;
    public string $code;
    public ?string $description;
    public ?string $logo;
    public string $type;
    public bool $isActive;
    public bool $isVisible;
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
