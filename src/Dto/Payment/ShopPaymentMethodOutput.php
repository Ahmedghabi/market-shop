<?php

namespace App\Dto\Payment;

final class ShopPaymentMethodOutput
{
    public string $id;
    public string $boutiqueId;
    public string $paymentMethodId;
    public string $name;
    public string $code;
    public ?string $description;
    public ?string $logo;
    public string $type;
    public bool $isActive;
    public bool $isGloballyActive;
    public bool $isVisible;
    public int $displayOrder;
    public ?int $minimumAmountCents;
    public ?int $maximumAmountCents;
    public bool $isSandbox;
    public bool $hasUsername;
    public bool $hasPassword;
    public bool $hasApiKey;
    public bool $hasSecretKey;
    public bool $hasWebhookSecret;
    /** @var array{bankName?: ?string, accountHolder?: ?string, iban?: ?string, swift?: ?string, paymentInstructions?: ?string} */
    public array $gatewayConfig = [];
    public \DateTimeImmutable $createdAt;
    public ?\DateTimeImmutable $updatedAt;
}
