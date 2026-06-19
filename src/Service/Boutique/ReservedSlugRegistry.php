<?php

namespace App\Service\Boutique;

final class ReservedSlugRegistry
{
    /** @var list<string> */
    private const DEFAULT_RESERVED = [
        'admin', 'api', 'www', 'mail', 'ftp', 'localhost',
        'support', 'blog', 'cdn', 'assets', 'staging', 'dev',
        'shop', 'boutique', 'store', 'demo', 'test',
        'root', 'app', 'auth', 'login', 'register',
        'help', 'faq', 'about', 'contact', 'legal',
        'docs', 'status', 'health', 'metrics', 'monitor',
        'dashboard', 'backoffice', 'bo', 'panel',
        'payment', 'pay', 'checkout', 'cart',
        'moncompte', 'account', 'profile', 'user', 'users',
        'search', 'notification', 'notifications',
        'webhook', 'webhooks', 'api', 'graphql',
        'super', 'superadmin', 'admin',
    ];

    /** @var list<string> */
    private array $reserved;

    /** @param list<string> $additional */
    public function __construct(array $additional = [])
    {
        $this->reserved = array_values(array_unique([
            ...self::DEFAULT_RESERVED,
            ...$additional,
        ]));
    }

    public function isReserved(string $slug): bool
    {
        return in_array(strtolower(trim($slug)), $this->reserved, true);
    }

    /** @return list<string> */
    public function getAll(): array
    {
        return $this->reserved;
    }
}
