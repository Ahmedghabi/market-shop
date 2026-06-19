<?php

namespace App\Service\Session;

use App\Entity\User;
use App\Entity\UserSession;
use App\Enum\SessionDeviceType;
use App\Repository\UserRepository;
use App\Repository\UserSessionRepository;
use App\Service\AppConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class SessionService
{
    public function __construct(
        private UserSessionRepository $sessions,
        private UserRepository $users,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private TokenStorageInterface $tokenStorage,
        private AppConfigService $appConfig,
        private SessionCacheService $cache,
    ) {
    }

    public function create(User $user, string $tokenId): UserSession
    {
        $config = $this->sessionConfig();
        $activeSessions = $this->sessions->findActiveByUser($user);
        $limit = (int) ($config['max_sessions'] ?? 0);
        if ($limit > 0 && count($activeSessions) >= $limit) {
            $behavior = (string) ($config['limit_behavior'] ?? 'REJECT');
            if ('DROP_OLDEST' === strtoupper($behavior)) {
                $oldest = array_slice(array_reverse($activeSessions), 0, 1)[0] ?? null;
                if ($oldest instanceof UserSession) {
                    $oldest->deactivate();
                }
            } else {
                throw new AccessDeniedHttpException('Maximum active sessions reached.');
            }
        }

        foreach ($activeSessions as $session) {
            $session->markCurrent(false);
        }

        $session = new UserSession(
            user: $user,
            tokenId: $tokenId,
            deviceName: $this->deviceName(),
            deviceType: $this->deviceType(),
            browser: $this->browser(),
            operatingSystem: $this->operatingSystem(),
            ipAddress: $this->requestStack->getCurrentRequest()?->getClientIp(),
            country: null,
            city: null,
            lastActivityAt: new \DateTimeImmutable(),
            createdAt: new \DateTimeImmutable(),
            expiresAt: (new \DateTimeImmutable())->modify(sprintf('+%d days', (int) ($config['ttl_days'] ?? 30))),
            isCurrent: true,
            isActive: true,
        );
        $this->em->persist($session);
        $this->em->flush();
        $this->cache->invalidateUserSessions($user->getId()->toRfc4122());

        return $session;
    }

    /** @return array{identifier:string,roles:list<string>,tokenId:?string} */
    public function validate(string $tokenId, string $identifier, array $roles): array
    {
        $session = $this->sessions->findOneByTokenId($tokenId);
        if (!$session instanceof UserSession || !$session->isActive() || $session->isExpiredAt(new \DateTimeImmutable())) {
            throw new AccessDeniedHttpException('Session expired or inactive.');
        }

        if ($session->getUser()->getUserIdentifier() !== $identifier) {
            throw new AccessDeniedHttpException('Session user mismatch.');
        }

        $session->touch();
        $session->markCurrent(true);
        $this->em->flush();
        $this->cache->invalidateUserSessions($session->getUser()->getId()->toRfc4122());

        return [
            'identifier' => $identifier,
            'roles' => $roles,
            'tokenId' => $tokenId,
        ];
    }

    /** @return list<array<string, mixed>> */
    public function listForCurrentUser(): array
    {
        $user = $this->currentUser();
        $currentTokenId = $this->currentTokenId();

        return $this->cache->getUserSessions($user->getId()->toRfc4122(), function () use ($user, $currentTokenId): array {
            return array_map(fn (UserSession $session) => [
                'id' => (string) $session->getId(),
                'tokenId' => $session->getTokenId(),
                'deviceName' => $session->getDeviceName(),
                'deviceType' => $session->getDeviceType()->value,
                'browser' => $session->getBrowser(),
                'operatingSystem' => $session->getOperatingSystem(),
                'ipAddress' => $session->getIpAddress(),
                'country' => $session->getCountry(),
                'city' => $session->getCity(),
                'lastActivityAt' => $session->getLastActivityAt()->format('c'),
                'createdAt' => $session->getCreatedAt()->format('c'),
                'expiresAt' => $session->getExpiresAt()->format('c'),
                'isCurrent' => $session->getTokenId() === $currentTokenId,
                'isActive' => $session->isActive(),
            ], $this->sessions->findActiveByUser($user));
        });
    }

    public function deleteSession(string $sessionId): void
    {
        $user = $this->currentUser();
        $session = $this->sessions->find($sessionId);
        if (!$session instanceof UserSession || $session->getUser()->getId() != $user->getId()) {
            throw new NotFoundHttpException('Session not found');
        }

        $allowCurrent = (bool) ($this->sessionConfig()['allow_current_session_deletion'] ?? false);
        if (!$allowCurrent && $session->getTokenId() === $this->currentTokenId()) {
            throw new AccessDeniedHttpException('Current session cannot be deleted.');
        }

        $session->deactivate();
        $this->em->flush();
        $this->cache->invalidateUserSessions($user->getId()->toRfc4122());
    }

    public function deleteAllSessions(): void
    {
        $user = $this->currentUser();
        $keepCurrent = !(bool) ($this->sessionConfig()['allow_current_session_deletion'] ?? false);
        $currentTokenId = $this->currentTokenId();

        foreach ($this->sessions->findActiveByUser($user) as $session) {
            if ($keepCurrent && $session->getTokenId() === $currentTokenId) {
                continue;
            }
            $session->deactivate();
        }
        $this->em->flush();
        $this->cache->invalidateUserSessions($user->getId()->toRfc4122());
    }

    public function cleanupExpired(): int
    {
        return $this->sessions->deleteExpired(new \DateTimeImmutable());
    }

    private function currentUser(): User
    {
        $securityUser = $this->tokenStorage->getToken()?->getUser();
        $identifier = $securityUser?->getUserIdentifier();
        $user = is_string($identifier) ? $this->users->findOneBy(['identifier' => $identifier]) : null;
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException('User not found.');
        }

        return $user;
    }

    private function currentTokenId(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request?->attributes->getString('_user_session_token_id') ?: null;
    }

    /** @return array<string, mixed> */
    private function sessionConfig(): array
    {
        return array_replace([
            'max_sessions' => 0,
            'limit_behavior' => 'REJECT',
            'invalidate_other_sessions_on_password_change' => true,
            'ttl_days' => 30,
            'remember_me_enabled' => false,
            'allow_current_session_deletion' => false,
        ], $this->appConfig->section('sessions'));
    }

    private function browser(): ?string
    {
        $ua = (string) $this->requestStack->getCurrentRequest()?->headers->get('User-Agent', '');
        foreach (['Edge', 'Chrome', 'Firefox', 'Safari'] as $browser) {
            if (str_contains($ua, $browser)) {
                return $browser;
            }
        }

        return null;
    }

    private function operatingSystem(): ?string
    {
        $ua = (string) $this->requestStack->getCurrentRequest()?->headers->get('User-Agent', '');
        foreach (['Windows', 'Android', 'iPhone', 'iPad', 'Mac OS X', 'Linux'] as $os) {
            if (str_contains($ua, $os)) {
                return $os;
            }
        }

        return null;
    }

    private function deviceType(): SessionDeviceType
    {
        $ua = (string) $this->requestStack->getCurrentRequest()?->headers->get('User-Agent', '');
        if (str_contains($ua, 'iPad') || str_contains($ua, 'Tablet')) {
            return SessionDeviceType::Tablet;
        }
        if (str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone')) {
            return SessionDeviceType::Mobile;
        }
        if ('' !== $ua) {
            return SessionDeviceType::Desktop;
        }

        return SessionDeviceType::Unknown;
    }

    private function deviceName(): ?string
    {
        $browser = $this->browser();
        $os = $this->operatingSystem();

        return trim(implode(' ', array_filter([$browser, $os])));
    }
}
