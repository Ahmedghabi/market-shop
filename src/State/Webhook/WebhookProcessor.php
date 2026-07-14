<?php

namespace App\State\Webhook;

use App\Dto\Webhook\WebhookInput;
use App\Dto\Webhook\WebhookOutput;
use App\Entity\Webhook;
use App\Service\Webhook\WebhookService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final class WebhookProcessor implements ProcessorInterface
{
    public function __construct(
        private WebhookService $webhookService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof \ApiPlatform\Metadata\Delete) {
            $webhook = $this->webhookService->getWebhookById($uriVariables['id'] ?? '');
            if ($webhook) {
                $this->webhookService->delete($webhook);
            }

            return null;
        }

        if ($data instanceof WebhookInput) {
            if (isset($uriVariables['id'])) {
                $webhook = $this->webhookService->getWebhookById($uriVariables['id']);
                if ($webhook) {
                    return $this->toOutput($this->webhookService->update($webhook, (array) $data));
                }
            }

            return $this->toOutput($this->webhookService->create((array) $data));
        }

        return null;
    }

    private function toOutput(Webhook $webhook): WebhookOutput
    {
        return new WebhookOutput(
            id: (string) $webhook->getId(),
            boutiqueId: $webhook->getBoutique() ? (string) $webhook->getBoutique()->getId() : null,
            url: $webhook->getUrl(),
            events: $webhook->getEvents(),
            secret: $webhook->getSecret() ? '***' : null,
            status: $webhook->getStatus(),
            lastTriggeredAt: $webhook->getLastTriggeredAt()?->format('c'),
            failureCount: $webhook->getFailureCount(),
            createdAt: $webhook->getCreatedAt()->format('c'),
            updatedAt: $webhook->getUpdatedAt()?->format('c'),
        );
    }
}
