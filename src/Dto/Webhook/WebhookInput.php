<?php

namespace App\Dto\Webhook;

final class WebhookInput
{
    public ?string $url = null;
    public ?array $events = null;
    public ?string $secret = null;
    public ?string $boutiqueId = null;
    public ?string $status = null;
}
