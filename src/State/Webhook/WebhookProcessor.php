<?php

namespace App\State\Webhook;

use App\Dto\Webhook\WebhookInput;
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
                    return $this->webhookService->update($webhook, (array) $data);
                }
            }

            return $this->webhookService->create((array) $data);
        }

        return null;
    }
}
