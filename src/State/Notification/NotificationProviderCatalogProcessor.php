<?php

namespace App\State\Notification;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Notification\NotificationProviderInput;
use App\Dto\Notification\NotificationProviderOutput;
use App\Entity\NotificationProvider;
use App\Enum\NotificationChannel;
use App\Repository\NotificationProviderRepository;
use App\Service\Notification\NotificationCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<NotificationProviderOutput|null> */
final readonly class NotificationProviderCatalogProcessor implements ProcessorInterface
{
    public function __construct(
        private NotificationProviderRepository $providers,
        private EntityManagerInterface $em,
        private NotificationProviderCatalogProvider $provider,
        private NotificationCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?NotificationProviderOutput
    {
        unset($context);

        if ($operation instanceof Delete) {
            $entity = $this->find((string) ($uriVariables['id'] ?? ''));
            $this->em->remove($entity);
            $this->em->flush();
            $this->cache->invalidateGlobal();

            return null;
        }

        assert($data instanceof NotificationProviderInput);
        $entity = isset($uriVariables['id']) ? $this->find((string) $uriVariables['id']) : new NotificationProvider(
            $data->code,
            $data->name,
            NotificationChannel::tryFrom($data->type) ?? NotificationChannel::Email,
            $data->isActive,
        );

        if (!isset($uriVariables['id'])) {
            $this->em->persist($entity);
        }

        $entity->setCode($data->code);
        $entity->setName($data->name);
        $entity->setType(NotificationChannel::tryFrom($data->type) ?? NotificationChannel::Email);
        $entity->setIsActive($data->isActive);

        $this->em->flush();
        $this->cache->invalidateGlobal();

        return $this->provider->provide(new \ApiPlatform\Metadata\Get(), ['id' => (string) $entity->getId()]);
    }

    private function find(string $id): NotificationProvider
    {
        $entity = $this->providers->find($id);
        if (!$entity instanceof NotificationProvider) {
            throw new NotFoundHttpException('Notification provider not found');
        }

        return $entity;
    }
}
