<?php

namespace App\Dto\ExtensionRequest;

final class ExtensionRequestOutput
{
    public ?string $id = null;
    public ?string $boutiqueId = null;
    public ?string $boutiqueName = null;
    public ?string $extensionId = null;
    public ?string $extensionCode = null;
    public ?string $extensionName = null;
    public ?string $extensionType = null;
    public int $priceTnd = 0;
    public string $status = 'draft';
    public ?string $comment = null;
    public ?string $adminComment = null;
    public ?string $invoiceId = null;
    public ?string $requestedAt = null;
    public ?string $paidAt = null;
    public ?string $decidedAt = null;
    public ?string $decidedBy = null;
    public ?string $grantId = null;
    public ?string $grantExpiresAt = null;
}
