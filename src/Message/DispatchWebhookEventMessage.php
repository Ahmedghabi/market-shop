<?php

namespace App\Message;

final readonly class DispatchWebhookEventMessage
{
    public function __construct(
        private string $eventName,
        private array $payload,
        private ?string $boutiqueId = null,
    ) {
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getBoutiqueId(): ?string
    {
        return $this->boutiqueId;
    }
}
