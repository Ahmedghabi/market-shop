<?php

namespace App\Dto\Payment;

final class ShopPaymentMethodInput
{
    public string $paymentMethodId;
    public bool $isActive = true;
    public int $displayOrder = 0;
    public ?int $minimumAmountCents = null;
    public ?int $maximumAmountCents = null;
    public ?string $username = null;
    public ?string $password = null;
    public ?string $apiKey = null;
    public ?string $secretKey = null;
    public ?string $webhookSecret = null;
    public bool $isSandbox = false;
    public ?string $bankName = null;
    public ?string $accountHolder = null;
    public ?string $iban = null;
    public ?string $swift = null;
    public ?string $paymentInstructions = null;
}
