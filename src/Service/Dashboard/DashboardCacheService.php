<?php

namespace App\Service\Dashboard;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class DashboardCacheService
{
    private const int TTL = 300;

    public function __construct(private CacheInterface $cache)
    {
    }

    /** @return array<string, mixed> */
    public function platform(callable $loader): array
    {
        return $this->cache->get('platform.dashboard', function (ItemInterface $item) use ($loader): array {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }

    /** @return array<string, mixed> */
    public function boutique(string $boutiqueId, callable $loader): array
    {
        return $this->cache->get("shop.{$boutiqueId}.dashboard", function (ItemInterface $item) use ($loader): array {
            $item->expiresAfter(self::TTL);

            return $loader();
        });
    }
}
