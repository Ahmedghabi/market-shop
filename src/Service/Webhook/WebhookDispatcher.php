<?php

namespace App\Service\Webhook;

use App\Entity\Webhook;
use App\Enum\WebhookEventType;

final readonly class WebhookDispatcher
{
    public function dispatch(Webhook $webhook, WebhookEventType $event, array $payload): void
    {
        $body = json_encode([
            'event' => $event->value,
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'data' => $payload,
        ]);

        $headers = [
            'Content-Type: application/json',
            'X-Webhook-Event: '.$event->value,
            'X-Webhook-Delivery: '.bin2hex(random_bytes(16)),
        ];

        if (null !== $webhook->getSecret()) {
            $signature = hash_hmac('sha256', $body, $webhook->getSecret());
            $headers[] = 'X-Webhook-Signature: sha256='.$signature;
        }

        $ch = curl_init($webhook->getUrl());
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode >= 200 && $httpCode < 300) {
            $webhook->markTriggered();
        } else {
            $webhook->markFailed();
        }
    }
}
