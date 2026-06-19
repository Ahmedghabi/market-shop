<?php

namespace App\State\Notification;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Notification\NotificationProviderOutput;
use App\Entity\NotificationProvider;
use App\Repository\NotificationProviderRepository;

/** @implements ProviderInterface<NotificationProviderOutput> */
final readonly class NotificationProviderCatalogProvider implements ProviderInterface
{
    public function __construct(private NotificationProviderRepository $providers)
    {
    }

    /** @return list<NotificationProviderOutput>|NotificationProviderOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|NotificationProviderOutput|null
    {
        unset($operation, $context);

        if (isset($uriVariables['id'])) {
            $provider = $this->providers->find((string) $uriVariables['id']);

            return $provider instanceof NotificationProvider ? $this->toOutput($provider) : null;
        }

        return array_map([$this, 'toOutput'], $this->providers->findBy([], ['name' => 'ASC']));
    }

    private function toOutput(NotificationProvider $provider): NotificationProviderOutput
    {
        $output = new NotificationProviderOutput();
        $output->id = (string) $provider->getId();
        $output->code = $provider->getCode();
        $output->name = $provider->getName();
        $output->type = $provider->getType()->value;
        $output->isActive = $provider->isActive();

        return $output;
    }
}
