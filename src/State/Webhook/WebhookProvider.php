<?php

namespace App\State\Webhook;

use App\Dto\Webhook\WebhookOutput;
use App\Entity\Webhook;
use App\Repository\WebhookRepository;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

final class WebhookProvider implements ProviderInterface
{
    public function __construct(
        private WebhookRepository $webhooks,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?WebhookOutput
    {
        $webhook = $this->webhooks->find($uriVariables['id'] ?? null);
        if (!$webhook instanceof Webhook) {
            return null;
        }

        return $this->toOutput($webhook);
    }

    /** @return list<WebhookOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $webhooks = $this->webhooks->findAllAdmin();

        return array_map($this->toOutput(...), $webhooks);
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
