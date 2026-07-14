<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\SubscriptionRequest\SubscriptionRequestOutput;
use App\Entity\Boutique;
use App\Entity\SubscriptionRequest;
use App\Repository\BoutiqueRepository;
use App\Repository\UserRepository;
use App\Security\BoutiqueContext;
use App\Service\Audit\AuditLogService;
use App\Service\NotificationService;
use App\Service\Subscription\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<SubscriptionRequestOutput> */
final class SubscriptionRenewProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BoutiqueContext $context,
        private readonly BoutiqueRepository $boutiques,
        private readonly UserRepository $users,
        private readonly SubscriptionManager $subscriptionManager,
        private readonly EntityManagerInterface $em,
        private readonly NotificationService $notifications,
        private readonly AuditLogService $auditLog,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SubscriptionRequestOutput
    {
        unset($data, $operation, $uriVariables, $context);

        $boutique = $this->resolveBoutique();
        $plan = $this->subscriptionManager->getCurrentPlan($boutique);
        if (null === $plan) {
            throw new BadRequestHttpException('Aucun plan actif à renouveler.');
        }

        $entity = new SubscriptionRequest($boutique, $plan);
        $this->em->persist($entity);
        $this->em->flush();

        $this->notifyBoutique($boutique, 'subscription_renewal_requested', 'Demande de renouvellement', sprintf('Votre demande de renouvellement du plan "%s" a été enregistrée.', $plan->getName()));
        $this->notifySuperAdmins('subscription_renewal_new', 'Renouvellement demandé', sprintf('%s a demandé le renouvellement du plan "%s".', $boutique->getName(), $plan->getName()));
        $this->auditLog->log(
            actorEmail: $this->context->getUserIdentifier() ?? 'system',
            actorRole: 'ROLE_BOUTIQUE_ADMIN',
            action: 'subscription.renewal_requested',
            resourceType: 'SubscriptionRequest',
            resourceId: (string) $entity->getId(),
            details: ['planId' => (string) $plan->getId(), 'planName' => $plan->getName()],
            boutiqueId: (string) $boutique->getId(),
        );

        $output = new SubscriptionRequestOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $boutique->getId();
        $output->boutiqueName = $boutique->getName();
        $output->subscriptionPlanId = (string) $plan->getId();
        $output->subscriptionPlanName = $plan->getName();
        $output->status = $entity->getStatus()->value;
        $output->requestedAt = $entity->getRequestedAt()->format('c');

        return $output;
    }

    private function resolveBoutique(): Boutique
    {
        $boutiqueId = $this->context->getBoutiqueId();
        if (null === $boutiqueId) {
            throw new NotFoundHttpException('Boutique introuvable.');
        }

        $boutique = $this->boutiques->find((string) $boutiqueId);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique introuvable.');
        }

        if (!$this->context->canAccessBoutique($boutique)) {
            throw new NotFoundHttpException('Accès refusé.');
        }

        return $boutique;
    }

    private function notifyBoutique(Boutique $boutique, string $type, string $title, string $message): void
    {
        $this->notifications->notify(null, $type, $title, $message, $boutique);
    }

    private function notifySuperAdmins(string $type, string $title, string $message): void
    {
        foreach ($this->users->findByRole('ROLE_SUPER_ADMIN') as $admin) {
            $this->notifications->notify($admin->getUserIdentifier(), $type, $title, $message);
        }
    }
}
