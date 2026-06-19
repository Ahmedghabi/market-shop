<?php

namespace App\Service\Session;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class SessionCacheService
{
    private const int TTL = 300;

    public function __construct(private CacheInterface $cache)
    {
    }

    public function getUserSessions(string $userId, callable $loader): mixed
    {
        return $this->cache->get("user.{$userId}.sessions", function (ItemInterface $item) use ($loader): mixed {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    public function invalidateUserSessions(string $userId): void
    {
        $this->cache->delete("user.{$userId}.sessions");
    }
}
