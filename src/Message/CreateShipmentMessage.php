<?php

namespace App\Message;

final readonly class CreateShipmentMessage
{
    public function __construct(
        private string $orderId,
        private ?string $accountId = null,
    ) {
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }
}
