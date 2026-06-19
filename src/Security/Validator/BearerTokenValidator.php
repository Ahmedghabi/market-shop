<?php

namespace App\Security\Validator;

use App\Security\LocalTokenManager;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BearerTokenValidator
{
    private const DEV_SUPER_ADMIN_TOKEN = 'dev-super-admin-token';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LocalTokenManager $localTokenManager,
        private string $oauth2Issuer,
        private string $oauth2Audience,
        private string $oauth2JwksUri,
        private readonly bool $kernelDebug = false,
    ) {
    }

    /** @return array{identifier: string, roles: list<string>, tokenId:?string} */
    public function validate(string $token): array
    {
        if ('' === trim($token)) {
            throw new BadCredentialsException('Empty bearer token.');
        }

        if (self::DEV_SUPER_ADMIN_TOKEN === $token) {
            if (!$this->kernelDebug) {
                throw new BadCredentialsException('Dev token rejected (kernel.debug = false).');
            }

            return [
                'identifier' => 'super-admin@market-shop.local',
                'roles' => ['ROLE_SUPER_ADMIN'],
                'tokenId' => null,
            ];
        }

        if (str_starts_with($token, 'local.')) {
            return $this->localTokenManager->validate($token);
        }

        [$header, $claims, $signedPayload, $signature] = $this->decodeJwt($token);

        if (($header['alg'] ?? null) === 'HS256') {
            return $this->localTokenManager->validate($token);
        }

        if (($header['alg'] ?? null) !== 'RS256') {
            throw new BadCredentialsException('Unsupported OAuth2 token algorithm.');
        }

        $kid = $header['kid'] ?? null;
        if (!is_string($kid) || '' === $kid) {
            throw new BadCredentialsException('Missing OAuth2 token key id.');
        }

        $this->verifySignature($kid, $signedPayload, $signature);
        $this->verifyClaims($claims);

        $subject = $claims['sub'] ?? null;
        if (!is_string($subject) || '' === $subject) {
            throw new BadCredentialsException('Missing OAuth2 token subject.');
        }

        return [
            'identifier' => $subject,
            'roles' => ['ROLE_USER'],
            'tokenId' => null,
        ];
    }

    /** @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: string, 3: string} */
    private function decodeJwt(string $token): array
    {
        $parts = explode('.', $token);
        if (3 !== count($parts)) {
            throw new BadCredentialsException('Invalid OAuth2 JWT format.');
        }

        [$encodedHeader, $encodedClaims, $encodedSignature] = $parts;

        try {
            $header = json_decode($this->base64UrlDecode($encodedHeader), true, 512, JSON_THROW_ON_ERROR);
            $claims = json_decode($this->base64UrlDecode($encodedClaims), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new BadCredentialsException('Invalid OAuth2 JWT JSON.', previous: $exception);
        }

        if (!is_array($header) || !is_array($claims)) {
            throw new BadCredentialsException('Invalid OAuth2 JWT payload.');
        }

        return [$header, $claims, $encodedHeader.'.'.$encodedClaims, $this->base64UrlDecode($encodedSignature)];
    }

    private function verifySignature(string $kid, string $signedPayload, string $signature): void
    {
        $jwk = $this->findJwk($kid);
        $publicKey = $this->createPemFromRsaJwk($jwk);

        $verified = openssl_verify($signedPayload, $signature, $publicKey, OPENSSL_ALGO_SHA256);
        if (1 !== $verified) {
            throw new BadCredentialsException('Invalid OAuth2 token signature.');
        }
    }

    /** @param array<string, mixed> $claims */
    private function verifyClaims(array $claims): void
    {
        $now = time();

        if (($claims['iss'] ?? null) !== $this->oauth2Issuer) {
            throw new BadCredentialsException('Invalid OAuth2 token issuer.');
        }

        $audience = $claims['aud'] ?? null;
        $audiences = is_array($audience) ? $audience : [$audience];
        if (!in_array($this->oauth2Audience, $audiences, true)) {
            throw new BadCredentialsException('Invalid OAuth2 token audience.');
        }

        if (isset($claims['exp']) && is_numeric($claims['exp']) && (int) $claims['exp'] <= $now) {
            throw new BadCredentialsException('Expired OAuth2 token.');
        }

        if (isset($claims['nbf']) && is_numeric($claims['nbf']) && (int) $claims['nbf'] > $now) {
            throw new BadCredentialsException('OAuth2 token is not valid yet.');
        }
    }

    /** @return array<string, mixed> */
    private function findJwk(string $kid): array
    {
        try {
            $payload = $this->httpClient->request('GET', $this->oauth2JwksUri)->getContent();
            $jwks = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new BadCredentialsException('Unable to load OAuth2 JWKS.', previous: $exception);
        }

        foreach (($jwks['keys'] ?? []) as $jwk) {
            if (is_array($jwk) && ($jwk['kid'] ?? null) === $kid) {
                return $jwk;
            }
        }

        throw new BadCredentialsException('OAuth2 signing key not found.');
    }

    /** @param array<string, mixed> $jwk */
    private function createPemFromRsaJwk(array $jwk): string
    {
        if (($jwk['kty'] ?? null) !== 'RSA' || !is_string($jwk['n'] ?? null) || !is_string($jwk['e'] ?? null)) {
            throw new BadCredentialsException('Invalid OAuth2 RSA JWK.');
        }

        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);
        $rsaPublicKey = "\x30".$this->asn1Length(
            "\x02".$this->asn1Length($this->unsignedInteger($modulus)).$this->unsignedInteger($modulus)
            ."\x02".$this->asn1Length($this->unsignedInteger($exponent)).$this->unsignedInteger($exponent)
        );
        $algorithmIdentifier = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
        $bitString = "\x03".$this->asn1Length("\x00".$rsaPublicKey)."\x00".$rsaPublicKey;
        $sequence = "\x30".$this->asn1Length($algorithmIdentifier.$bitString).$algorithmIdentifier.$bitString;

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($sequence), 64, "\n")."-----END PUBLIC KEY-----\n";
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

    private function asn1Length(string $value): string
    {
        $length = strlen($value);
        if ($length < 128) {
            return chr($length);
        }

        $hex = dechex($length);
        if (strlen($hex) % 2) {
            $hex = '0'.$hex;
        }

        return chr(0x80 + strlen($hex) / 2).hex2bin($hex);
    }

    private function unsignedInteger(string $value): string
    {
        return ord($value[0]) > 0x7F ? "\x00".$value : $value;
    }
}
