<?php

namespace App\State\Notification;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Notification\NotificationTemplateOutput;
use App\Entity\NotificationTemplate;
use App\Repository\NotificationTemplateRepository;
use App\Security\BoutiqueContext;
use App\Service\Notification\NotificationCacheService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<NotificationTemplateOutput> */
final readonly class NotificationTemplateProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private NotificationTemplateRepository $templates,
        private BoutiqueContext $context,
        private Security $security,
        private NotificationCacheService $cache,
    ) {
    }

    /** @return list<NotificationTemplateOutput>|NotificationTemplateOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|NotificationTemplateOutput|null
    {
        unset($operation);

        if (isset($uriVariables['id'])) {
            $template = $this->templates->find((string) $uriVariables['id']);

            return $template instanceof NotificationTemplate ? $this->toOutput($template) : null;
        }

        if (isset($uriVariables['boutiqueId'])) {
            $boutique = $this->resolveBoutiqueFromRequest($context);
            if (!$boutique instanceof \App\Entity\Boutique || !$this->context->canAccessBoutique($boutique)) {
                return [];
            }

            return $this->cache->get('shop.'.(string) $boutique->getId().'.notification.templates', fn (): array => array_map([$this, 'toOutput'], $this->templates->findBy(['boutique' => $boutique], ['createdAt' => 'DESC'])));
        }

        if (!$this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return [];
        }

        return $this->cache->get('notification.templates.global', fn (): array => array_map([$this, 'toOutput'], $this->templates->findBy(['boutique' => null], ['createdAt' => 'DESC'])));
    }

    private function toOutput(NotificationTemplate $template): NotificationTemplateOutput
    {
        $output = new NotificationTemplateOutput();
        $output->id = (string) $template->getId();
        $output->boutiqueId = $template->getBoutique() ? (string) $template->getBoutique()->getId() : null;
        $output->eventCode = $template->getEventCode();
        $output->channel = $template->getChannel()->value;
        $output->subject = $template->getSubject();
        $output->content = $template->getContent();
        $output->isActive = $template->isActive();

        return $output;
    }
}
