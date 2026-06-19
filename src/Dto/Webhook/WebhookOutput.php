<?php

namespace App\Dto\Webhook;

final class WebhookOutput
{
    public function __construct(
        public string $id,
        public ?string $boutiqueId,
        public string $url,
        public array $events,
        public ?string $secret,
        public string $status,
        public ?string $lastTriggeredAt,
        public int $failureCount,
        public string $createdAt,
        public ?string $updatedAt,
    ) {
    }
}
