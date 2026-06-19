<?php

namespace App\MessageHandler;

use App\Message\DispatchWebhookEventMessage;
use App\Service\Webhook\WebhookService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DispatchWebhookEventMessageHandler
{
    public function __construct(
        private WebhookService $webhookService,
    ) {
    }

    public function __invoke(DispatchWebhookEventMessage $message): void
    {
        $this->webhookService->dispatchEventSync(
            eventName: $message->getEventName(),
            payload: $message->getPayload(),
            boutiqueId: $message->getBoutiqueId(),
        );
    }
}
