<?php

namespace App\Security;

use App\Security\Validator\BearerTokenValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

final class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly BearerTokenValidator $tokenValidator)
    {
    }

    public function supports(Request $request): ?bool
    {
        return str_starts_with((string) $request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $token = substr((string) $request->headers->get('Authorization'), 7);
        $identity = $this->tokenValidator->validate($token);
        if (is_string($identity['tokenId'] ?? null)) {
            $request->attributes->set('_user_session_token_id', $identity['tokenId']);
        }

        return new SelfValidatingPassport(new UserBadge(
            $identity['identifier'],
            static fn (string $userIdentifier): InMemoryUser => new InMemoryUser($userIdentifier, null, $identity['roles']),
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new Response('Authentication failed.', Response::HTTP_UNAUTHORIZED);
    }
}
