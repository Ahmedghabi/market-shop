<?php

namespace App\EventSubscriber;

use App\Enum\BoutiqueStatus;
use App\Service\Boutique\SubdomainResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class BoutiqueRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SubdomainResolver $resolver,
        private AuthorizationCheckerInterface $auth,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['resolveBoutique', 10],
        ];
    }

    public function resolveBoutique(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Only resolve for API routes
        $path = $request->getPathInfo();
        if (!str_starts_with($path, '/api/')) {
            return;
        }

        // Skip admin routes
        if (str_starts_with($path, '/api/admin')) {
            return;
        }

        // Skip health check
        if ('/api/health' === $path) {
            return;
        }

        $boutique = $this->resolver->resolveFromRequest($request);
        if (null === $boutique) {
            return;
        }

        $request->attributes->set('_boutique', $boutique);
        $request->attributes->set('_boutique_id', $boutique->getId());

        $isAdmin = $this->isAuthenticatedAdmin();
        $status = $boutique->getStatus();

        // PENDING boutiques: only accessible by admins
        if (BoutiqueStatus::Pending === $status && !$isAdmin) {
            throw new AccessDeniedHttpException('Cette boutique est en attente d\'approbation.');
        }

        // SUSPENDED boutiques: still accessible by admins for management
        if (BoutiqueStatus::Suspended === $status && !$isAdmin) {
            throw new AccessDeniedHttpException('Cette boutique est suspendue.');
        }

        // REJECTED boutiques: not accessible publicly
        if (BoutiqueStatus::Rejected === $status && !$isAdmin) {
            throw new NotFoundHttpException('Page non trouvée');
        }

        // ARCHIVED boutiques: not accessible
        if (BoutiqueStatus::Archived === $status && !$isAdmin) {
            throw new NotFoundHttpException('Page non trouvée');
        }
    }

    private function isAuthenticatedAdmin(): bool
    {
        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->auth->isGranted('ROLE_BOUTIQUE_ADMIN');
    }
}
