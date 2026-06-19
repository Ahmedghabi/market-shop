<?php

namespace App\Service\Notification;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class NotificationCacheService
{
    private const int TTL = 21600;

    public function __construct(private CacheInterface $cache)
    {
    }

    public function get(string $key, callable $loader): mixed
    {
        return $this->cache->get($key, function (ItemInterface $item) use ($loader): mixed {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    public function invalidateByBoutique(string $boutiqueId): void
    {
        $this->cache->delete("shop.{$boutiqueId}.notification.templates");
        $this->cache->delete("shop.{$boutiqueId}.notification.config");
    }

    public function invalidateGlobal(): void
    {
        $this->cache->delete('notification.providers');
        $this->cache->delete('notification.templates.global');
    }
}
