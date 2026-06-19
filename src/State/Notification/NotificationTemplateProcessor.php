<?php

namespace App\State\Notification;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Notification\NotificationTemplateInput;
use App\Dto\Notification\NotificationTemplateOutput;
use App\Entity\NotificationTemplate;
use App\Enum\NotificationChannel;
use App\Repository\BoutiqueRepository;
use App\Repository\NotificationTemplateRepository;
use App\Security\BoutiqueContext;
use App\Service\Notification\NotificationCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<NotificationTemplateOutput|null> */
final readonly class NotificationTemplateProcessor implements ProcessorInterface
{
    public function __construct(
        private NotificationTemplateRepository $templates,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private EntityManagerInterface $em,
        private NotificationTemplateProvider $provider,
        private NotificationCacheService $cache,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?NotificationTemplateOutput
    {
        unset($context);

        if ($operation instanceof Delete) {
            $entity = $this->find((string) ($uriVariables['id'] ?? ''));
            $boutiqueId = $entity->getBoutique()?->getId()?->toRfc4122();
            $this->em->remove($entity);
            $this->em->flush();
            if ($boutiqueId) {
                $this->cache->invalidateByBoutique($boutiqueId);
            } else {
                $this->cache->invalidateGlobal();
            }

            return null;
        }

        assert($data instanceof NotificationTemplateInput);
        $boutique = null;
        if ($data->boutiqueId || isset($uriVariables['boutiqueId'])) {
            $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? $data->boutiqueId));
            if ($boutique && !$this->context->canAccessBoutique($boutique)) {
                throw new AccessDeniedHttpException('Access denied');
            }
        }

        $entity = isset($uriVariables['id']) ? $this->find((string) $uriVariables['id']) : new NotificationTemplate(
            $boutique,
            $data->eventCode,
            NotificationChannel::tryFrom($data->channel) ?? NotificationChannel::Email,
            $data->subject,
            $data->content,
            $data->isActive,
        );
        if (!isset($uriVariables['id'])) {
            $this->em->persist($entity);
        }

        $entity->setEventCode($data->eventCode);
        $entity->setChannel(NotificationChannel::tryFrom($data->channel) ?? NotificationChannel::Email);
        $entity->setSubject($data->subject);
        $entity->setContent($data->content);
        $entity->setIsActive($data->isActive);

        $this->em->flush();
        if ($boutique) {
            $this->cache->invalidateByBoutique((string) $boutique->getId());
        } else {
            $this->cache->invalidateGlobal();
        }

        return $this->provider->provide(new \ApiPlatform\Metadata\Get(), ['id' => (string) $entity->getId()]);
    }

    private function find(string $id): NotificationTemplate
    {
        $entity = $this->templates->find($id);
        if (!$entity instanceof NotificationTemplate) {
            throw new NotFoundHttpException('Notification template not found');
        }

        return $entity;
    }
}
