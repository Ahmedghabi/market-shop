<?php

namespace App\Service\Delivery;

final class EncryptionService
{
    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16;

    private string $key;

    public function __construct(
        #[\SensitiveParameter] string $appSecret,
    ) {
        $this->key = hash_hkdf('sha256', $appSecret, 32, 'delivery-credentials');
    }

    public function encrypt(#[\SensitiveParameter] string $plaintext): string
    {
        $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
        $tag = '';

        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);

        if (false === $ciphertext) {
            throw new \RuntimeException('Encryption failed');
        }

        return base64_encode($iv.$tag.$ciphertext);
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload, true);

        if (false === $decoded) {
            throw new \RuntimeException('Invalid encrypted payload');
        }

        $ivLen = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($decoded, 0, $ivLen);
        $tag = substr($decoded, $ivLen, self::TAG_LENGTH);
        $ciphertext = substr($decoded, $ivLen + self::TAG_LENGTH);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $plaintext) {
            throw new \RuntimeException('Decryption failed');
        }

        return $plaintext;
    }
}
