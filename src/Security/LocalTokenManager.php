<?php

namespace App\Security;

use App\Entity\User;
use App\Service\Session\SessionService;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Uid\Uuid;

final readonly class LocalTokenManager
{
    public function __construct(
        private string $appSecret,
        private SessionService $sessions,
    ) {
    }

    public function create(User $user): string
    {
        $tokenId = Uuid::v7()->toRfc4122();
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'sub' => $user->getUserIdentifier(),
            'email' => $user->getUserIdentifier(),
            'displayName' => $user->getDisplayName(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'roles' => $user->getRoles(),
            'tid' => $tokenId,
            'iat' => time(),
            'exp' => time() + 86400,
        ], JSON_THROW_ON_ERROR));
        $signedPayload = $header.'.'.$payload;
        $signature = $this->sign($signedPayload);
        $this->sessions->create($user, $tokenId);

        return $signedPayload.'.'.$signature;
    }

    /** @return array{identifier: string, roles: list<string>, tokenId:?string} */
    public function validate(string $token): array
    {
        [$payload, $signature, $signedPayload] = $this->parse($token);
        if (!hash_equals($this->sign($signedPayload), $signature)) {
            throw new BadCredentialsException('Invalid local token signature.');
        }

        try {
            $claims = json_decode($this->base64UrlDecode($payload), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new BadCredentialsException('Invalid local token JSON.', previous: $exception);
        }

        if (!is_array($claims)) {
            throw new BadCredentialsException('Invalid local token payload.');
        }

        if (isset($claims['exp']) && is_numeric($claims['exp']) && (int) $claims['exp'] <= time()) {
            throw new BadCredentialsException('Expired local token.');
        }

        $identifier = $claims['sub'] ?? null;
        if (!is_string($identifier) || '' === $identifier) {
            throw new BadCredentialsException('Missing local token subject.');
        }

        $roles = $claims['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [];
        }

        $tokenId = $claims['tid'] ?? null;
        if (null !== $tokenId && (!is_string($tokenId) || '' === $tokenId)) {
            throw new BadCredentialsException('Invalid local token session id.');
        }

        return $this->sessions->validate(
            is_string($tokenId) ? $tokenId : '',
            $identifier,
            array_values(array_filter($roles, static fn (mixed $role): bool => is_string($role))),
        ) + [
            'identifier' => $identifier,
            'roles' => array_values(array_filter($roles, static fn (mixed $role): bool => is_string($role))),
            'tokenId' => is_string($tokenId) ? $tokenId : null,
        ];
    }

    private function sign(string $payload): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $payload, $this->appSecret, true));
    }

    /** @return array{0: string, 1: string, 2: string} */
    private function parse(string $token): array
    {
        $parts = explode('.', $token);
        if (3 !== count($parts)) {
            throw new BadCredentialsException('Invalid local token format.');
        }

        if ('local' === $parts[0]) {
            return [$parts[1], $parts[2], $parts[1]];
        }

        return [$parts[1], $parts[2], $parts[0].'.'.$parts[1]];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padding = strlen($value) % 4;
        if ($padding > 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if (false === $decoded) {
            throw new BadCredentialsException('Invalid base64url value.');
        }

        return $decoded;
    }
}
