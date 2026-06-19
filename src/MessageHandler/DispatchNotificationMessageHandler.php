<?php

namespace App\MessageHandler;

use App\Entity\NotificationLog;
use App\Enum\NotificationChannel;
use App\Enum\NotificationLogStatus;
use App\Message\DispatchNotificationMessage;
use App\Repository\BoutiqueRepository;
use App\Repository\NotificationTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DispatchNotificationMessageHandler
{
    public function __construct(
        private NotificationTemplateRepository $templates,
        private BoutiqueRepository $boutiques,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(DispatchNotificationMessage $message): void
    {
        $boutique = $message->boutiqueId ? $this->boutiques->findBySlugOrId($message->boutiqueId) : null;
        $channel = NotificationChannel::tryFrom($message->channel) ?? NotificationChannel::Internal;
        $log = new NotificationLog($boutique, $channel, $message->recipient, $message->eventCode, NotificationLogStatus::Pending);
        $this->em->persist($log);

        try {
            $template = $this->templates->findActiveTemplate($boutique, $message->eventCode, $channel);
            if (null === $template) {
                $log->markFailed('Template not found');
                $this->em->flush();

                return;
            }

            $rendered = $template->getContent();
            foreach ($message->variables as $key => $value) {
                $rendered = str_replace('{{'.$key.'}}', (string) $value, $rendered);
            }

            // Placeholder async delivery hook: provider-specific sending can be plugged here.
            unset($rendered);
            $log->markSent();
        } catch (\Throwable $e) {
            $log->markFailed($e->getMessage());
        }

        $this->em->flush();
    }
}
