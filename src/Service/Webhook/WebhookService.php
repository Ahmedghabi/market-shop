<?php

namespace App\Service\Webhook;

use App\Entity\Webhook;
use App\Enum\WebhookEventType;
use App\Message\DispatchWebhookEventMessage;
use App\Repository\WebhookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class WebhookService
{
    public function __construct(
        private WebhookRepository $webhooks,
        private EntityManagerInterface $em,
        private WebhookDispatcher $dispatcher,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Dispatch event by string name — used by processors/services.
     */
    public function dispatchEvent(string $eventName, array $payload, ?string $boutiqueId = null): void
    {
        $this->messageBus->dispatch(new DispatchWebhookEventMessage($eventName, $payload, $boutiqueId));
    }

    /**
     * Dispatch synchronously — used by the async handler.
     */
    public function dispatchEventSync(string $eventName, array $payload, ?string $boutiqueId = null): void
    {
        $eventType = WebhookEventType::tryFrom($eventName);
        if (null === $eventType) {
            return;
        }

        $webhooks = $this->webhooks->findListeningTo($eventType, $boutiqueId);

        foreach ($webhooks as $webhook) {
            $this->dispatcher->dispatch($webhook, $eventType, $payload);
        }
    }

    public function create(array $data): Webhook
    {
        $boutique = null;
        if (isset($data['boutiqueId'])) {
            $boutique = $this->em->find(\App\Entity\Boutique::class, $data['boutiqueId']);
        }

        $webhook = new Webhook(
            boutique: $boutique,
            url: (string) ($data['url'] ?? ''),
            events: (array) ($data['events'] ?? []),
        );

        if (isset($data['secret'])) {
            $webhook->setSecret((string) $data['secret']);
        }

        $this->em->persist($webhook);
        $this->em->flush();

        return $webhook;
    }

    public function update(Webhook $webhook, array $data): Webhook
    {
        if (isset($data['url'])) {
            $webhook->setUrl((string) $data['url']);
        }
        if (isset($data['events'])) {
            $webhook->setEvents((array) $data['events']);
        }
        if (isset($data['secret'])) {
            $webhook->setSecret((string) $data['secret']);
        }
        if (isset($data['status'])) {
            $webhook->setStatus((string) $data['status']);
        }

        $this->em->flush();

        return $webhook;
    }

    public function delete(Webhook $webhook): void
    {
        $this->em->remove($webhook);
        $this->em->flush();
    }

    public function getWebhooks(): array
    {
        return $this->webhooks->findAllAdmin();
    }

    public function getWebhookById(string $webhookId): ?Webhook
    {
        return $this->webhooks->find($webhookId);
    }
}
