<?php

namespace App\Service\Favorite;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class FavoriteCacheService
{
    private const int TTL = 21600;

    public function __construct(private CacheInterface $cache)
    {
    }

    /** @template T */
    public function get(string $key, callable $loader): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($loader): mixed {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    public function delete(string $key): void
    {
        $this->cache->delete($key);
    }

    public function productUserKey(string $userId): string
    {
        return "user.{$userId}.favorites_products";
    }

    public function shopUserKey(string $userId): string
    {
        return "user.{$userId}.favorites_shops";
    }

    public function productSessionKey(string $sessionId): string
    {
        return "session.{$sessionId}.favorites_products";
    }

    public function shopSessionKey(string $sessionId): string
    {
        return "session.{$sessionId}.favorites_shops";
    }
}
